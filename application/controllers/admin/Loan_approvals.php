<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Loan Approvals (maker-checker queue).
 * Ports application/controllers/api/v1/Loan_approval.php's approve()/
 * reject()/override()/pending() into web screens so APPRAISER/BRANCH_MANAGER/
 * REGIONAL_MANAGER staff can work the queue from a browser, not just the
 * mobile app. Business rules (maker != checker, approval-limit escalation,
 * required remarks) are ported verbatim -- see that controller for the
 * canonical logic this mirrors.
 */
class Loan_approvals extends Admin_Controller
{
    private static $STAGE_ORDER = array('APPRAISER', 'MANAGER', 'REGIONAL_MANAGER', 'HO');

    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_approval_workflow_model', 'workflows');
        $this->load->model('Loan_approval_limit_model', 'approval_limits');
        $this->load->model('Loan_approval_log_model', 'approval_logs');
        $this->load->model('Branch_model', 'branches');
    }

    /** GET /admin/loan-approvals */
    public function index()
    {
        $role_code = $this->user['role_code'];
        $default_stage = $role_code === 'BRANCH_MANAGER' ? 'MANAGER' : ($role_code === 'REGIONAL_MANAGER' ? 'REGIONAL_MANAGER' : 'APPRAISER');
        $stage = $this->input->get('stage') ?: $default_stage;

        $pending = $this->db->select('loans.*, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name')
            ->from('loans')
            ->join('loan_approval_workflows', 'loan_approval_workflows.loan_id = loans.id')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->where('loan_approval_workflows.current_stage', $stage)
            ->where('loan_approval_workflows.status', 'PENDING')
            ->order_by('loans.created_at', 'ASC')
            ->get()
            ->result_array();

        $this->render('loan_approvals', array(
            'page_title' => 'Loan Approvals',
            'pending' => $pending,
            'stage' => $stage,
            'stages' => self::$STAGE_ORDER,
            'can_override' => in_array($role_code, array('REGIONAL_MANAGER', 'ADMIN'), true),
        ));
    }

    /** POST /admin/loan-approvals/(:num)/approve */
    public function approve($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        $workflow = $this->workflows->for_loan($loan_id);
        if (! $workflow || $workflow['status'] !== 'PENDING') {
            return $this->_fail('No pending approval for this loan.');
        }

        if ((int) $this->user['id'] === (int) $loan['created_by']) {
            return $this->_fail('Maker cannot approve their own submission.');
        }

        $limit = $this->approval_limits->for_role($this->user['role_id']);

        if ($limit && (float) $loan['sanctioned_amount'] > (float) $limit['max_amount']) {
            $current_index = array_search($workflow['current_stage'], self::$STAGE_ORDER, true);
            $next_stage = ($current_index !== false && isset(self::$STAGE_ORDER[$current_index + 1]))
                ? self::$STAGE_ORDER[$current_index + 1]
                : 'HO';

            $this->workflows->update($workflow['id'], array('current_stage' => $next_stage));

            $this->approval_logs->insert(array(
                'loan_id' => $loan_id,
                'stage' => $next_stage,
                'action' => 'ESCALATED',
                'actioned_by' => $this->user['id'],
            ));

            $this->session->set_flashdata('status', "Escalated to {$next_stage} (exceeds your approval limit).");
            redirect('admin/loan-approvals');

            return;
        }

        $this->workflows->update($workflow['id'], array('status' => 'APPROVED'));
        $this->loans->update($loan_id, array('status' => 'APPROVED'));

        $this->approval_logs->insert(array(
            'loan_id' => $loan_id,
            'stage' => $workflow['current_stage'],
            'action' => 'APPROVE',
            'actioned_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $loan_id, 'APPROVE', array('status' => $loan['status']), array('status' => 'APPROVED'));

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' approved.');
        redirect('admin/loan-approvals');
    }

    /** POST /admin/loan-approvals/(:num)/reject */
    public function reject($loan_id)
    {
        $remarks = trim((string) $this->input->post('remarks'));
        if ($remarks === '') {
            return $this->_fail('Remarks are required to reject a loan.');
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        $workflow = $this->workflows->for_loan($loan_id);
        if ($workflow) {
            $this->workflows->update($workflow['id'], array('status' => 'REJECTED'));
        }

        $this->loans->update($loan_id, array('status' => 'REJECTED'));

        $this->approval_logs->insert(array(
            'loan_id' => $loan_id,
            'stage' => $workflow ? $workflow['current_stage'] : 'APPRAISER',
            'action' => 'REJECT',
            'actioned_by' => $this->user['id'],
            'remarks' => $remarks,
        ));

        $this->audit_log('Loan', $loan_id, 'REJECT', array('status' => $loan['status']), array('status' => 'REJECTED', 'remarks' => $remarks));

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' rejected.');
        redirect('admin/loan-approvals');
    }

    /** POST /admin/loan-approvals/(:num)/override -- REGIONAL_MANAGER/ADMIN only */
    public function override($loan_id)
    {
        if (! $this->require_admin_role(array('REGIONAL_MANAGER'))) {
            return;
        }

        $remarks = trim((string) $this->input->post('remarks'));
        if ($remarks === '') {
            return $this->_fail('Remarks are required to override an approval.');
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        $this->loans->update($loan_id, array('status' => 'APPROVED'));

        $workflow = $this->workflows->for_loan($loan_id);
        if ($workflow) {
            $this->workflows->update($workflow['id'], array('status' => 'APPROVED'));
        }

        $this->approval_logs->insert(array(
            'loan_id' => $loan_id,
            'stage' => 'OVERRIDE',
            'action' => 'OVERRIDE',
            'actioned_by' => $this->user['id'],
            'remarks' => $remarks,
        ));

        $this->audit_log('Loan', $loan_id, 'OVERRIDE', array('status' => $loan['status']), array('status' => 'APPROVED', 'remarks' => $remarks));

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' approval overridden.');
        redirect('admin/loan-approvals');
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/loan-approvals');
    }
}
