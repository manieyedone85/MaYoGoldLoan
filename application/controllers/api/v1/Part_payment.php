<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/PartPaymentController.php.
 * Routes:
 *   POST /api/v1/loan/{loan}/part-payment -- role:CASHIER
 *   POST /api/v1/loan/{loan}/reload       -- auth + device binding only
 */
class Part_payment extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_part_payment_model', 'part_payments');
        $this->load->model('Loan_reload_model', 'reloads');
    }

    /** POST /api/v1/loan/{loan}/part-payment */
    public function part_payment($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();

        if (isset($data['principal_amount']) && (! is_numeric($data['principal_amount']) || (float) $data['principal_amount'] < 0)) {
            return json_error('principal_amount must be a non-negative number.');
        }
        if (isset($data['interest_amount']) && (! is_numeric($data['interest_amount']) || (float) $data['interest_amount'] < 0)) {
            return json_error('interest_amount must be a non-negative number.');
        }

        $principal_amount = (float) ($data['principal_amount'] ?? 0);
        $interest_amount = (float) ($data['interest_amount'] ?? 0);

        $this->db->trans_start();

        $payment_id = $this->part_payments->insert(array(
            'loan_id' => $loan['id'],
            'principal_amount' => $principal_amount,
            'interest_amount' => $interest_amount,
            'collected_by' => $user['id'],
        ));

        if ($principal_amount > 0) {
            $new_sanctioned_amount = (float) $loan['sanctioned_amount'] - $principal_amount;
            $this->loans->update($loan['id'], array(
                'sanctioned_amount' => $new_sanctioned_amount,
                'status' => 'PART_PAID',
            ));

            $this->audit_log('Loan', $loan['id'], 'PART_PAYMENT',
                array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
                array('status' => 'PART_PAID', 'sanctioned_amount' => $new_sanctioned_amount, 'payment_id' => $payment_id, 'principal_amount' => $principal_amount, 'interest_amount' => $interest_amount)
            );
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Part payment failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->part_payments->find($payment_id)), 201);
    }

    /** POST /api/v1/loan/{loan}/reload */
    public function reload($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();

        if (! isset($data['excess_amount_eligible']) || ! is_numeric($data['excess_amount_eligible']) || (float) $data['excess_amount_eligible'] < 0) {
            return json_error('excess_amount_eligible is required and must be a non-negative number.');
        }
        if (! isset($data['reload_amount']) || ! is_numeric($data['reload_amount']) || (float) $data['reload_amount'] < 0) {
            return json_error('reload_amount is required and must be a non-negative number.');
        }

        $this->db->trans_start();

        $reload_id = $this->reloads->insert(array(
            'loan_id' => $loan['id'],
            'excess_amount_eligible' => $data['excess_amount_eligible'],
            'reload_amount' => $data['reload_amount'],
            'processed_by' => $user['id'],
        ));

        $new_sanctioned_amount = (float) $loan['sanctioned_amount'] + (float) $data['reload_amount'];
        $this->loans->update($loan['id'], array(
            'sanctioned_amount' => $new_sanctioned_amount,
        ));

        $this->audit_log('Loan', $loan['id'], 'RELOAD',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('sanctioned_amount' => $new_sanctioned_amount, 'reload_id' => $reload_id, 'reload_amount' => $data['reload_amount'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Reload failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->reloads->find($reload_id)), 201);
    }
}
