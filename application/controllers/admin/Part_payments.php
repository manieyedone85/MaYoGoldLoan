<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Part Payments & Re-loans. Ports
 * application/controllers/api/v1/Part_payment.php's part_payment()/reload()
 * -- same status gate (ACTIVE/PART_PAID/RENEWED), same atomic
 * increment/decrement of sanctioned_amount, same server-recomputed
 * excess-eligible cap on reload_amount.
 */
class Part_payments extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('CASHIER'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_part_payment_model', 'part_payments');
        $this->load->model('Loan_reload_model', 'reloads');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Gold_rate_model', 'gold_rates');
    }

    /** GET /admin/part-payments */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $matches = $search !== '' ? $this->loans->search_by_account_or_mobile($search) : array();

        foreach ($matches as &$loan) {
            $loan['excess_amount_eligible'] = $this->_current_excess_amount_eligible($loan);
        }
        unset($loan);

        $this->render('part_payments', array(
            'page_title' => 'Part Payments & Re-loans',
            'search' => $search,
            'matches' => $matches,
            'payment_history' => $this->part_payments->with_relations(50),
            'reload_history' => $this->reloads->with_relations(50),
        ));
    }

    /** POST /admin/part-payments/(:num)/pay */
    public function pay($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans can accept a part payment.');
        }

        $principal_amount = (float) $this->input->post('principal_amount');
        $interest_amount = (float) $this->input->post('interest_amount');

        if ($principal_amount < 0 || $interest_amount < 0) {
            return $this->_fail('Principal and interest amounts must be non-negative.');
        }

        $this->db->trans_start();

        $payment_id = $this->part_payments->insert(array(
            'loan_id' => $loan['id'],
            'principal_amount' => $principal_amount,
            'interest_amount' => $interest_amount,
            'collected_by' => $this->user['id'],
        ));

        if ($principal_amount > 0) {
            $this->db->set('sanctioned_amount', 'sanctioned_amount - ' . number_format($principal_amount, 2, '.', ''), false)
                ->where('id', $loan['id'])
                ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
                ->update('loans', array('status' => 'PART_PAID', 'updated_at' => date('Y-m-d H:i:s')));

            if ($this->db->affected_rows() < 1) {
                $this->db->trans_rollback();

                return $this->_fail('Loan is no longer ACTIVE/PART_PAID/RENEWED (state changed). Please refresh.');
            }

            $updated_loan = $this->loans->find($loan['id']);

            $this->audit_log('Loan', $loan['id'], 'PART_PAYMENT',
                array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
                array('status' => 'PART_PAID', 'sanctioned_amount' => $updated_loan['sanctioned_amount'], 'payment_id' => $payment_id)
            );
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Part payment failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Part payment recorded for loan #' . $loan_id . '.');
        redirect('admin/part-payments');
    }

    /** POST /admin/part-payments/(:num)/reload */
    public function reload_loan($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return $this->_fail('Only ACTIVE, PART_PAID, or RENEWED loans are eligible for a re-loan.');
        }

        $reload_amount = $this->input->post('reload_amount');
        if (! is_numeric($reload_amount) || (float) $reload_amount < 0) {
            return $this->_fail('Reload amount is required and must be a non-negative number.');
        }

        $excess_amount_eligible = $this->_current_excess_amount_eligible($loan);
        if ((float) $reload_amount > $excess_amount_eligible) {
            return $this->_fail("Reload amount exceeds the eligible excess gold value ({$excess_amount_eligible}).");
        }

        $this->db->trans_start();

        $reload_id = $this->reloads->insert(array(
            'loan_id' => $loan['id'],
            'excess_amount_eligible' => $excess_amount_eligible,
            'reload_amount' => $reload_amount,
            'previous_sanctioned_amount' => $loan['sanctioned_amount'],
            'processed_by' => $this->user['id'],
        ));

        $this->db->set('sanctioned_amount', 'sanctioned_amount + ' . number_format((float) $reload_amount, 2, '.', ''), false)
            ->where('id', $loan['id'])
            ->update('loans', array('updated_at' => date('Y-m-d H:i:s')));

        $updated_loan = $this->loans->find($loan['id']);

        $this->audit_log('Loan', $loan['id'], 'RELOAD',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('sanctioned_amount' => $updated_loan['sanctioned_amount'], 'reload_id' => $reload_id, 'reload_amount' => $reload_amount)
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Reload failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Re-loan recorded for loan #' . $loan_id . '.');
        redirect('admin/part-payments');
    }

    /** Mirrors Part_payment::_current_excess_amount_eligible(). */
    private function _current_excess_amount_eligible($loan)
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
        redirect('admin/part-payments');
    }
}
