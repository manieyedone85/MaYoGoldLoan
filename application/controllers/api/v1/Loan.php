<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/LoanController.php (calculate, store,
 * emiSchedule). Loan approval workflow lives in Loan_approval.php.
 */
class Loan extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Loan_charge_model', 'loan_charges');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Branch_model', 'branches');
    }

    /** POST /api/v1/loan/calculate */
    public function calculate()
    {
        $this->require_auth();

        $data = $this->json_input();
        $error = $this->_validate_calculate_input($data);
        if ($error) {
            return json_error($error);
        }

        $product = $this->loan_products->find($data['loan_product_id']);
        $eligibleAmount = $this->jewellery_items->sum_eligible_amount($data['jewellery_item_ids']);

        $processingFee = round($eligibleAmount * ($product['processing_fee_pct'] / 100), 2);
        $gst = round($processingFee * ($product['gst_pct'] / 100), 2);
        $insurance = round($eligibleAmount * ($product['insurance_pct'] / 100), 2);
        $netDisbursed = $eligibleAmount - $processingFee - $gst - $insurance;

        return json_response(array(
            'eligible_amount' => $eligibleAmount,
            'interest_rate_pct' => $product['interest_rate_pct'],
            'interest_type' => $product['interest_type'],
            'tenure_months' => $product['tenure_months'],
            'processing_fee' => $processingFee,
            'gst_amount' => $gst,
            'insurance_amount' => $insurance,
            'net_disbursed_amount' => $netDisbursed,
        ));
    }

    /** POST /api/v1/loan  (create in DRAFT, then submit-for-approval) */
    public function store()
    {
        $user = $this->require_auth();

        $data = $this->json_input();
        $error = $this->_validate_calculate_input($data);
        if ($error) {
            return json_error($error);
        }

        $product = $this->loan_products->find($data['loan_product_id']);
        $eligibleAmount = $this->jewellery_items->sum_eligible_amount($data['jewellery_item_ids']);

        $processingFee = round($eligibleAmount * ($product['processing_fee_pct'] / 100), 2);
        $gst = round($processingFee * ($product['gst_pct'] / 100), 2);
        $insurance = round($eligibleAmount * ($product['insurance_pct'] / 100), 2);
        $netDisbursed = $eligibleAmount - $processingFee - $gst - $insurance;

        $this->db->trans_start();

        $loanId = $this->loans->insert(array(
            'loan_account_number' => $this->loans->next_loan_account_number(),
            'customer_id' => $data['customer_id'],
            'branch_id' => $data['branch_id'],
            'loan_product_id' => $product['id'],
            'eligible_amount' => $eligibleAmount,
            'sanctioned_amount' => $eligibleAmount,
            'interest_rate_pct' => $product['interest_rate_pct'],
            'processing_fee' => $processingFee,
            'gst_amount' => $gst,
            'insurance_amount' => $insurance,
            'net_disbursed_amount' => $netDisbursed,
            'loan_date' => date('Y-m-d'),
            'due_date' => $this->_add_months(date('Y-m-d'), (int) $product['tenure_months']),
            'status' => 'DRAFT',
            'created_by' => $user['id'],
        ));

        foreach (array(
            array('charge_type' => 'PROCESSING_FEE', 'amount' => $processingFee),
            array('charge_type' => 'GST', 'amount' => $gst),
            array('charge_type' => 'INSURANCE', 'amount' => $insurance),
        ) as $charge) {
            $charge['loan_id'] = $loanId;
            $this->loan_charges->insert($charge);
        }

        $this->jewellery_items->mark_pledged($data['jewellery_item_ids'], $loanId);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Unable to create loan.', 500);
        }

        $loan = $this->loans->find($loanId);
        $loan['charges'] = $this->loan_charges->for_loan($loanId);

        return json_response(array('data' => $loan), 201);
    }

    /** GET /api/v1/loan/{id}/emi-schedule */
    public function emi_schedule($loan_id)
    {
        $this->require_auth();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $product = $this->loan_products->find($loan['loan_product_id']);
        $months = (int) $product['tenure_months'];
        $monthlyInterest = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);

        $schedule = array();
        for ($m = 1; $m <= $months; $m++) {
            $schedule[] = array(
                'month' => $m,
                'due_date' => $this->_add_months($loan['loan_date'], $m),
                'interest_due' => $monthlyInterest,
            );
        }

        return json_response(array('data' => $schedule));
    }

    /** @return string|null error message, or null if valid */
    private function _validate_calculate_input($data)
    {
        if (empty($data['customer_id']) || ! $this->customers->find($data['customer_id'])) {
            return 'customer_id is required and must exist.';
        }
        if (empty($data['branch_id']) || ! $this->branches->find($data['branch_id'])) {
            return 'branch_id is required and must exist.';
        }
        if (empty($data['loan_product_id']) || ! $this->loan_products->find($data['loan_product_id'])) {
            return 'loan_product_id is required and must exist.';
        }
        if (empty($data['jewellery_item_ids']) || ! is_array($data['jewellery_item_ids'])) {
            return 'jewellery_item_ids is required and must be an array with at least one item.';
        }
        foreach ($data['jewellery_item_ids'] as $item_id) {
            if (! $this->jewellery_items->find($item_id)) {
                return "jewellery_item_ids contains an id ({$item_id}) that does not exist.";
            }
        }

        return null;
    }

    private function _add_months($date, $months)
    {
        $timestamp = strtotime($date);
        $day = (int) date('j', $timestamp);
        $timestamp = mktime(0, 0, 0, (int) date('n', $timestamp) + $months, $day, (int) date('Y', $timestamp));

        return date('Y-m-d', $timestamp);
    }
}
