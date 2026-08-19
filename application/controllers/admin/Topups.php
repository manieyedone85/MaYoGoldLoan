<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Top-ups. Ports application/controllers/api/v1/Topup.php's
 * eligibility()/add_jewellery()/approve()/disburse() -- note the API has no
 * separate "propose" stage: approve() itself is what creates an APPROVED
 * loan_topups row (BRANCH_MANAGER/REGIONAL_MANAGER), which a CASHIER then
 * disburses. Same server-recomputed eligible-topup ceiling, same atomic
 * guards on both writes.
 */
class Topups extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'CASHIER'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_topup_model', 'topups');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Gold_rate_model', 'gold_rates');
    }

    /** GET /admin/topups */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $matches = $search !== '' ? $this->loans->search_by_account_or_mobile($search) : array();

        foreach ($matches as &$loan) {
            $loan['eligible_topup_amount'] = $this->_current_eligible_topup($loan);
            $loan['unpledged_items'] = $this->jewellery_items->evaluated_unpledged_for_customer($loan['customer_id']);
        }
        unset($loan);

        $awaiting_page = max(1, (int) $this->input->get('awaiting_page'));
        $awaiting_result = $this->topups->with_relations('APPROVED', $search, 15, $awaiting_page);

        $history_page = max(1, (int) $this->input->get('history_page'));
        $history_result = $this->topups->with_relations('DISBURSED', $search, 15, $history_page);

        $this->render('topups', array(
            'page_title' => 'Top-ups',
            'search' => $search,
            'matches' => $matches,
            'role_code' => $this->user['role_code'],
            'awaiting_disbursement' => $awaiting_result['data'],
            'awaiting_pagination' => $awaiting_result,
            'awaiting_filters' => array('search' => $search),
            'history' => $history_result['data'],
            'history_pagination' => $history_result,
            'history_filters' => array('search' => $search),
        ));
    }

    /** POST /admin/topups/(:num)/add-jewellery -- role APPRAISER/BRANCH_MANAGER */
    public function add_jewellery($loan_id)
    {
        if (! $this->require_admin_role(array('APPRAISER', 'BRANCH_MANAGER'))) {
            return;
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans can have jewellery added.');
        }

        $item_ids = (array) $this->input->post('jewellery_item_ids');
        if (empty($item_ids)) {
            return $this->_fail('Select at least one jewellery item to add.');
        }

        $items = $this->jewellery_items->find_in($item_ids);
        if (count($items) !== count($item_ids)) {
            return $this->_fail('One of the selected jewellery items no longer exists.');
        }

        $added_eligible = 0.0;
        foreach ($items as $item) {
            if ($item['status'] !== 'EVALUATED') {
                return $this->_fail("Jewellery item {$item['id']} is not available to pledge (status {$item['status']}).");
            }
            if ((int) $item['customer_id'] !== (int) $loan['customer_id']) {
                return $this->_fail("Jewellery item {$item['id']} does not belong to this loan's customer.");
            }
            $added_eligible += (float) $item['eligible_amount'];
        }

        $this->db->trans_start();

        $this->jewellery_items->mark_pledged($item_ids, $loan_id);

        $new_eligible_amount = (float) $loan['eligible_amount'] + $added_eligible;
        $this->loans->update($loan_id, array('eligible_amount' => $new_eligible_amount));

        $this->audit_log('Loan', $loan_id, 'TOPUP_ADD_JEWELLERY',
            array('eligible_amount' => $loan['eligible_amount']),
            array('eligible_amount' => $new_eligible_amount, 'jewellery_item_ids' => $item_ids)
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Unable to add jewellery. Please retry.');
        }

        $this->session->set_flashdata('status', 'Jewellery added to loan #' . $loan_id . '. Eligible amount is now ₹' . number_format($new_eligible_amount, 2) . '.');
        redirect('admin/topups');
    }

    /** POST /admin/topups/(:num)/approve -- role BRANCH_MANAGER/REGIONAL_MANAGER */
    public function approve($loan_id)
    {
        if (! $this->require_admin_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER'))) {
            return;
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans are eligible for a top-up.');
        }

        $approved_amount = $this->input->post('approved_amount');
        if (! is_numeric($approved_amount) || (float) $approved_amount < 0) {
            return $this->_fail('Approved amount is required and must be a non-negative number.');
        }

        $eligible_topup_amount = $this->_current_eligible_topup($loan);
        if ((float) $approved_amount > $eligible_topup_amount) {
            return $this->_fail("Approved amount exceeds the eligible top-up amount ({$eligible_topup_amount}).");
        }

        $topup_id = $this->topups->insert(array(
            'loan_id' => $loan['id'],
            'eligible_topup_amount' => $eligible_topup_amount,
            'approved_amount' => $approved_amount,
            'status' => 'APPROVED',
            'approved_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $loan['id'], 'TOPUP_APPROVE',
            array('status' => $loan['status']),
            array('topup_id' => $topup_id, 'eligible_topup_amount' => $eligible_topup_amount, 'approved_amount' => $approved_amount)
        );

        $this->session->set_flashdata('status', 'Top-up of ₹' . number_format($approved_amount, 2) . ' approved for loan #' . $loan_id . '.');
        redirect('admin/topups');
    }

    /** POST /admin/topups/(:num)/disburse -- role CASHIER */
    public function disburse($loan_id)
    {
        if (! $this->require_admin_role(array('CASHIER'))) {
            return;
        }

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans can receive a top-up disbursement.');
        }

        $topup = $this->topups->latest_approved($loan['id']);
        if (! $topup) {
            return $this->_fail('No approved topup found for this loan.');
        }

        $this->db->trans_start();

        $this->db->where('id', $topup['id'])
            ->where('status', 'APPROVED')
            ->update('loan_topups', array(
                'status' => 'DISBURSED',
                'previous_sanctioned_amount' => $loan['sanctioned_amount'],
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return $this->_fail('This topup has already been disbursed or is no longer approved. Please refresh.');
        }

        $this->db->set('sanctioned_amount', 'sanctioned_amount + ' . number_format((float) $topup['approved_amount'], 2, '.', ''), false)
            ->where('id', $loan['id'])
            ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->update('loans');

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return $this->_fail('Loan is no longer ACTIVE/PART_PAID/RENEWED (state changed). Please refresh.');
        }

        $updated_loan = $this->loans->find($loan['id']);

        $this->audit_log('Loan', $loan['id'], 'TOPUP_DISBURSE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('sanctioned_amount' => $updated_loan['sanctioned_amount'], 'topup_id' => $topup['id'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Topup disbursement failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Top-up disbursed for loan #' . $loan_id . '.');
        redirect('admin/topups');
    }

    /** Mirrors Topup::_current_eligible_topup(). */
    private function _current_eligible_topup($loan)
    {
        $items = $this->jewellery_items->for_loan($loan['id']);

        $current_value = 0.0;
        foreach ($items as $item) {
            $latest_rate = $this->gold_rates->latest_approved($item['purity_karat']);
            if ($latest_rate) {
                $current_value += (float) $item['net_weight'] * (float) $latest_rate['rate_per_gram'] * ((float) $item['eligible_percentage'] / 100);
            }
        }

        return max(0, round($current_value - (float) $loan['sanctioned_amount'], 2));
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/topups');
    }
}
