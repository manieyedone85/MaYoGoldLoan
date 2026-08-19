<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Renewals. Ports
 * application/controllers/api/v1/Renewal.php's eligibility()/renew() --
 * same status gate (ACTIVE/PART_PAID/RENEWED), same monthly-interest-due
 * formula, same atomic status guard.
 */
class Renewals extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('CASHIER'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Loan_renewal_model', 'renewals');
    }

    /** GET /admin/renewals */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $matches = $search !== '' ? $this->loans->search_by_account_or_mobile($search) : array();

        foreach ($matches as &$loan) {
            $loan['eligible'] = in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true);
            $loan['interest_due'] = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        }
        unset($loan);

        $history_page = max(1, (int) $this->input->get('history_page'));
        $history_result = $this->renewals->with_relations($search, 15, $history_page);

        $this->render('renewals', array(
            'page_title' => 'Renewals',
            'search' => $search,
            'matches' => $matches,
            'history' => $history_result['data'],
            'history_pagination' => $history_result,
            'history_filters' => array('search' => $search),
        ));
    }

    /** POST /admin/renewals/(:num)/renew */
    public function renew($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans are eligible for renewal.');
        }

        $interest_paid = $this->input->post('interest_paid');
        if (! is_numeric($interest_paid) || (float) $interest_paid < 0) {
            return $this->_fail('Interest paid is required and must be a non-negative number.');
        }

        $renewal_charges = $this->input->post('renewal_charges');
        if ($renewal_charges !== '' && $renewal_charges !== null && (! is_numeric($renewal_charges) || (float) $renewal_charges < 0)) {
            return $this->_fail('Renewal charges must be a non-negative number.');
        }
        $renewal_charges = $renewal_charges !== '' && $renewal_charges !== null ? (float) $renewal_charges : 0;

        $interest_due = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        if ((float) $interest_paid + 0.01 < $interest_due) {
            return $this->_fail("Interest paid must cover the interest due of {$interest_due} to renew.");
        }

        $product = $this->loan_products->find($loan['loan_product_id']);
        $tenure = $product ? (int) $product['tenure_months'] : 0;
        $new_due_date = date('Y-m-d', strtotime('+' . $tenure . ' months'));

        $this->db->trans_start();

        $this->db->where('id', $loan['id'])
            ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->update('loans', array('status' => 'RENEWED', 'due_date' => $new_due_date, 'updated_at' => date('Y-m-d H:i:s')));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return $this->_fail('Loan is no longer ACTIVE/PART_PAID/RENEWED (state changed). Please refresh.');
        }

        $renewal_id = $this->renewals->insert(array(
            'loan_id' => $loan['id'],
            'renewed_tenure_months' => $tenure,
            'interest_paid' => $interest_paid,
            'renewal_charges' => $renewal_charges,
            'new_due_date' => $new_due_date,
            'previous_due_date' => $loan['due_date'],
            'processed_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $loan['id'], 'RENEW',
            array('status' => $loan['status'], 'due_date' => $loan['due_date']),
            array('status' => 'RENEWED', 'due_date' => $new_due_date, 'renewal_id' => $renewal_id)
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Renewal failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' renewed. New due date: ' . $new_due_date . '.');
        redirect('admin/renewals');
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/renewals');
    }
}
