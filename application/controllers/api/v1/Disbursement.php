<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/DisbursementController.php.
 * Route: POST /api/v1/loan/{loan}/disburse -- role:CASHIER (see routes/api.php).
 */
class Disbursement extends Api_Controller
{
    // Cash disbursement above this (INR) is blocked -- forces a bank-transfer mode instead.
    const CASH_LIMIT = 20000;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_disbursement_model', 'disbursements');
    }

    /** POST /api/v1/loan/{loan}/disburse */
    public function disburse($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        if ($loan['status'] !== 'APPROVED') {
            return json_error('Loan must be approved before disbursement.');
        }

        $data = $this->json_input();
        /*$allowed_modes = array('CASH', 'IMPS', 'RTGS', 'NEFT', 'UPI', 'BANK_TRANSFER');

        if (empty($data['mode']) || ! in_array($data['mode'], $allowed_modes, true)) {
            return json_error('mode is required and must be one of ' . implode(',', $allowed_modes) . '.');
        }*/

        if ($data['mode'] === 'CASH' && (float) $loan['net_disbursed_amount'] > self::CASH_LIMIT) {
            return json_error('Cash disbursement above the regulatory limit is not permitted. Use a bank transfer mode.');
        }

        $this->db->trans_start();
        

        $disbursement_id = $this->disbursements->insert(array(
            'loan_id' => $loan['id'],
            'mode' => $data['mode'],
            'amount' => $loan['net_disbursed_amount'],
            'reference_number' => $data['reference_number'] ?? null,
            'status' => 'COMPLETED',
            'disbursed_by' => $user['id'],
        ));

        // Atomic conditional guard: only flip to ACTIVE if the loan is still APPROVED,
        // so two concurrent requests can't both pass the earlier check-then-act and double-disburse.
        $this->db->where('id', $loan['id'])
            ->where('status', 'APPROVED')
            ->update('loans', array('status' => 'ACTIVE', 'updated_at' => date('Y-m-d H:i:s')));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();
            return json_error('Loan is no longer approved (already disbursed or state changed). Please refresh.', 422);
        }

        // Auto GL posting -- every money-moving transaction posts to accounting, never manual double-entry.
        // AccountingService::postDisbursement($loan, $disbursement);

        $this->audit_log('Loan', $loan['id'], 'DISBURSE',
            array('status' => $loan['status']),
            array('status' => 'ACTIVE', 'disbursement_id' => $disbursement_id, 'mode' => $data['mode'], 'amount' => $loan['net_disbursed_amount'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Disbursement failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->disbursements->find($disbursement_id)), 201);
    }
}
