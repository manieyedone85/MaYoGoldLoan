<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/LoanApprovalController.php.
 * Maker-Checker: actioning user must never equal loans.created_by.
 */
class Loan_approval extends Api_Controller
{
    private static $STAGE_ORDER = array('APPRAISER', 'MANAGER', 'REGIONAL_MANAGER', 'HO');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_approval_workflow_model', 'workflows');
        $this->load->model('Loan_approval_limit_model', 'approval_limits');
        $this->load->model('Loan_approval_log_model', 'approval_logs');
        $this->load->model('Role_model', 'roles');
    }

    /** POST /api/v1/loan/{id}/submit-for-approval */
    public function submit($loan_id)
    {
        $this->require_auth();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        if ($loan['status'] !== 'DRAFT') {
            return json_error('Only draft loans can be submitted.', 422);
        }

        $this->workflows->insert(array(
            'loan_id' => $loan_id,
            'current_stage' => 'APPRAISER',
            'status' => 'PENDING',
        ));

        $this->loans->update($loan_id, array('status' => 'PENDING_APPROVAL'));

        return json_response(array('message' => 'Submitted for approval.'));
    }

    /**
     * POST /api/v1/loan/{id}/approve  (role: APPRAISER, BRANCH_MANAGER, REGIONAL_MANAGER)
     * Maker-Checker: actioning user must never equal loans.created_by.
     */
    public function approve($loan_id)
    {
        $user = $this->require_auth();
        $this->require_role(array('APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER','ADMIN'));
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $workflow = $this->workflows->for_loan($loan_id);
        if (! $workflow || $workflow['status'] !== 'PENDING') {
            return json_error('No pending approval for this loan.', 422);
        }

        if ((int) $user['id'] === (int) $loan['created_by']) {
            return json_error('Maker cannot approve their own submission.', 403);
        }

        $limit = $this->approval_limits->for_role($user['role_id']);

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
                'actioned_by' => $user['id'],
            ));

            return json_response(array('message' => "Escalated to {$next_stage} (exceeds approval limit)."));
        }

        $this->workflows->update($workflow['id'], array('status' => 'APPROVED'));
        $this->loans->update($loan_id, array('status' => 'APPROVED'));

        $this->approval_logs->insert(array(
            'loan_id' => $loan_id,
            'stage' => $workflow['current_stage'],
            'action' => 'APPROVE',
            'actioned_by' => $user['id'],
        ));

        $this->audit_log('Loan', $loan_id, 'APPROVE',
            array('status' => $loan['status']),
            array('status' => 'APPROVED')
        );

        return json_response(array('message' => 'Loan approved.'));
    }

    /** POST /api/v1/loan/{id}/reject  (role: APPRAISER, BRANCH_MANAGER, REGIONAL_MANAGER) */
    public function reject($loan_id)
    {
        $user = $this->require_auth();
        $this->require_role(array('APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER','ADMIN'));
        $this->require_device_binding();

        $data = $this->json_input();
        if (empty($data['remarks'])) {
            return json_error('remarks is required.');
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
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
            'actioned_by' => $user['id'],
            'remarks' => $data['remarks'],
        ));

        $this->audit_log('Loan', $loan_id, 'REJECT',
            array('status' => $loan['status']),
            array('status' => 'REJECTED', 'remarks' => $data['remarks'])
        );

        return json_response(array('message' => 'Loan rejected.'));
    }

    /** POST /api/v1/loan/{id}/override  (role: REGIONAL_MANAGER, ADMIN) */
    public function override($loan_id)
    {
        $user = $this->require_auth();
        $this->require_role(array('REGIONAL_MANAGER', 'ADMIN'));

        $data = $this->json_input();
        if (empty($data['remarks'])) {
            return json_error('remarks is required.');
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
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
            'actioned_by' => $user['id'],
            'remarks' => $data['remarks'],
        ));

        $this->audit_log('Loan', $loan_id, 'OVERRIDE',
            array('status' => $loan['status']),
            array('status' => 'APPROVED', 'remarks' => $data['remarks'])
        );

        return json_response(array('message' => 'Loan approval overridden.'));
    }

    /** GET /api/v1/loan/pending-approval?stage= */
    public function pending()
    {
        $user = $this->require_auth();

        $role = $this->roles->find($user['role_id']);
        $role_code = $role ? $role['code'] : null;
        $stage = $role_code === 'BRANCH_MANAGER' ? 'MANAGER' : ($this->input->get('stage') ?: 'APPRAISER');

        $loans = $this->db->select('loans.*, customers.name AS customer_name, customers.mobile AS customer_mobile')
            ->from('loans')
            ->join('loan_approval_workflows', 'loan_approval_workflows.loan_id = loans.id')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->where('loan_approval_workflows.current_stage', $stage)
            ->where('loan_approval_workflows.status', 'PENDING')
            ->get()
            ->result_array();

        return json_response(array('data' => $loans));
    }
}
