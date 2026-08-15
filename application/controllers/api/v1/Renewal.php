<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/RenewalController.php.
 * Routes: GET /api/v1/loan/{loan}/renewal-eligibility, POST /api/v1/loan/{loan}/renew
 * (no role: middleware on these two in routes/api.php -- just auth + device binding).
 */
class Renewal extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Loan_renewal_model', 'renewals');
    }

    /** GET /api/v1/loan/{loan}/renewal-eligibility */
    public function eligibility($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        return json_response(array(
            'eligible' => in_array($loan['status'], array('ACTIVE', 'PART_PAID'), true),
            'interest_due' => round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100), 2),
        ));
    }

    /** POST /api/v1/loan/{loan}/renew */
    public function renew($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();

        if (! isset($data['interest_paid']) || ! is_numeric($data['interest_paid']) || (float) $data['interest_paid'] < 0) {
            return json_error('interest_paid is required and must be a non-negative number.');
        }

        if (isset($data['renewal_charges']) && (! is_numeric($data['renewal_charges']) || (float) $data['renewal_charges'] < 0)) {
            return json_error('renewal_charges must be a non-negative number.');
        }

        $product = $this->loan_products->find($loan['loan_product_id']);
        $tenure = $product ? (int) $product['tenure_months'] : 0;
        $new_due_date = date('Y-m-d', strtotime('+' . $tenure . ' months'));

        $this->db->trans_start();

        $renewal_id = $this->renewals->insert(array(
            'loan_id' => $loan['id'],
            'renewed_tenure_months' => $tenure,
            'interest_paid' => $data['interest_paid'],
            'renewal_charges' => $data['renewal_charges'] ?? 0,
            'new_due_date' => $new_due_date,
            'processed_by' => $user['id'],
        ));

        $this->loans->update($loan['id'], array('status' => 'RENEWED', 'due_date' => $new_due_date));

        $this->audit_log('Loan', $loan['id'], 'RENEW',
            array('status' => $loan['status'], 'due_date' => $loan['due_date']),
            array('status' => 'RENEWED', 'due_date' => $new_due_date, 'renewal_id' => $renewal_id)
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Renewal failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->renewals->find($renewal_id)), 201);
    }
}
