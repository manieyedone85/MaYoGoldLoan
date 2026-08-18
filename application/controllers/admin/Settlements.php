<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Settlements (foreclosure/closure). Ports
 * application/controllers/api/v1/Settlement.php's closure_statement()/
 * settle() -- same accrued-interest formula, same "collected amount must
 * meet the full payable-to-close figure" check, same atomic status guard.
 * Note: settling only marks the loan SETTLED -- it never releases
 * jewellery; that's Gold_releases's job (Gold_release::complete()), same as
 * the API.
 */
class Settlements extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('CASHIER', 'BRANCH_MANAGER'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_closure_model', 'closures');
        $this->load->model('Interest_collection_model', 'collections');
    }

    /** GET /admin/settlements */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $matches = $search !== '' ? $this->loans->search_by_account_or_mobile($search) : array();

        foreach ($matches as &$loan) {
            $loan['statement'] = $this->_compute_closure_statement($loan);
            $loan['eligible'] = in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true);
        }
        unset($loan);

        $this->render('settlements', array(
            'page_title' => 'Settlements',
            'search' => $search,
            'matches' => $matches,
            'history' => $this->closures->with_relations(50),
        ));
    }

    /** POST /admin/settlements/(:num)/settle */
    public function settle($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans can be settled.');
        }

        $total_amount_collected = $this->input->post('total_amount_collected');
        if (! is_numeric($total_amount_collected) || (float) $total_amount_collected < 0) {
            return $this->_fail('Total amount collected is required and must be a non-negative number.');
        }

        $statement = $this->_compute_closure_statement($loan);
        if ((float) $total_amount_collected + 0.01 < $statement['total_payable_to_close']) {
            return $this->_fail("Amount collected is short of the required closure amount ({$statement['total_payable_to_close']}).");
        }

        $this->db->trans_start();

        $this->db->where('id', $loan['id'])
            ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->update('loans', array('status' => 'SETTLED', 'updated_at' => date('Y-m-d H:i:s')));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return $this->_fail('Loan is no longer ACTIVE/PART_PAID/RENEWED (already settled or state changed). Please refresh.');
        }

        $closure_id = $this->closures->insert(array(
            'loan_id' => $loan['id'],
            'total_amount_collected' => $total_amount_collected,
            'closure_date' => date('Y-m-d'),
            'closed_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $loan['id'], 'SETTLE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('status' => 'SETTLED', 'closure_id' => $closure_id, 'total_amount_collected' => $total_amount_collected)
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Settlement failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' settled. Proceed to Gold Release to complete the checklist and release jewellery.');
        redirect('admin/settlements');
    }

    /** Mirrors Settlement::_compute_closure_statement(). */
    private function _compute_closure_statement($loan)
    {
        $interest_paid = $this->collections->total_collected($loan['id']);
        $months_elapsed = $this->_months_elapsed($loan['loan_date']);
        $monthly_interest = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        $pending_interest = max(0, round(($monthly_interest * $months_elapsed) - $interest_paid, 2));

        return array(
            'sanctioned_amount' => (float) $loan['sanctioned_amount'],
            'interest_paid' => $interest_paid,
            'pending_interest' => $pending_interest,
            'total_payable_to_close' => round((float) $loan['sanctioned_amount'] + $pending_interest, 2),
        );
    }

    /** GET /admin/settlements/(:num)/receipt -- printable customer-copy closure receipt. */
    public function receipt($closure_id)
    {
        $closure = $this->closures->find_with_relations($closure_id);
        if (! $closure) {
            show_404();

            return;
        }

        $this->render('settlement_receipt', array(
            'page_title' => 'Closure Receipt — ' . ($closure['loan_account_number'] ?? 'Loan #' . $closure['loan_id']),
            'closure' => $closure,
        ));
    }

    private function _months_elapsed($loan_date)
    {
        $start = new DateTime($loan_date);
        $now = new DateTime();
        $diff = $start->diff($now);

        return ($diff->y * 12) + $diff->m;
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/settlements');
    }
}
