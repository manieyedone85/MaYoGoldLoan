<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Interest Collections. Ports
 * application/controllers/api/v1/Interest.php's due()/collect()/receipt()
 * -- same monthly-interest formula, same allowed modes
 * (CASH/UPI/BANK_TRANSFER/CARD), same idempotency-key dedupe.
 */
class Interest_collections extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('CASHIER'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Interest_collection_model', 'collections');
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Branch_model', 'branches');
    }

    /** GET /admin/interest-collections */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $matches = $search !== '' ? $this->loans->search_by_account_or_mobile($search) : array();

        foreach ($matches as &$loan) {
            $loan['interest_due'] = $this->_interest_due($loan);
        }
        unset($loan);

        $this->render('interest_collections', array(
            'page_title' => 'Interest Collections',
            'search' => $search,
            'matches' => $matches,
            'history' => $this->collections->with_relations(50),
        ));
    }

    /** POST /admin/interest-collections/(:num)/collect */
    public function collect($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        $amount = $this->input->post('amount');
        if (! is_numeric($amount) || (float) $amount < 0.01) {
            return $this->_fail('Amount is required and must be at least 0.01.');
        }

        $mode = trim((string) $this->input->post('mode'));
        if (! in_array($mode, array('CASH', 'UPI', 'BANK_TRANSFER', 'CARD'), true)) {
            return $this->_fail('A valid payment mode is required.');
        }

        $collection_id = $this->collections->insert(array(
            'loan_id' => $loan['id'],
            'amount' => $amount,
            'mode' => $mode,
            'receipt_number' => 'RCPT' . strtoupper($this->_random_string(10)),
            'collected_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $loan['id'], 'INTEREST_COLLECT', null, array('collection_id' => $collection_id, 'amount' => $amount, 'mode' => $mode));

        $this->session->set_flashdata('status', 'Interest collected. Receipt: ' . $this->collections->find($collection_id)['receipt_number']);
        redirect('admin/interest-collections');
    }

    /** GET /admin/interest-collections/(:num)/receipt */
    public function receipt($collection_id)
    {
        $collection = $this->collections->find($collection_id);
        if (! $collection) {
            show_404();

            return;
        }

        $loan = $this->loans->find($collection['loan_id']);
        $customer = $loan ? $this->customers->find($loan['customer_id']) : null;
        $branch = $loan ? $this->branches->find($loan['branch_id']) : null;

        $this->render('interest_receipt', array(
            'page_title' => 'Receipt ' . $collection['receipt_number'],
            'collection' => $collection,
            'loan' => $loan,
            'customer' => $customer,
            'branch' => $branch,
        ));
    }

    /** Mirrors Interest::due() -- monthly interest x months elapsed, minus collected. */
    private function _interest_due($loan)
    {
        $months_elapsed = $this->_months_elapsed($loan['loan_date']);
        $monthly_interest = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        $total_paid = $this->collections->total_collected($loan['id']);

        return max(0, round(($monthly_interest * $months_elapsed) - $total_paid, 2));
    }

    private function _months_elapsed($loan_date)
    {
        $start = new DateTime($loan_date);
        $now = new DateTime();
        $diff = $start->diff($now);

        return ($diff->y * 12) + $diff->m;
    }

    private function _random_string($length)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $out;
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/interest-collections');
    }
}
