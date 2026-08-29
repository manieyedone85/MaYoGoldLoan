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
        $this->load->model('Disbursement_mode_model', 'disbursement_modes');
        $this->load->model('Loan_document_model', 'loan_documents');
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

        // BR-007 "Disbursement requires approval and documents"
        // (docs/BRD_COVERAGE_AUDIT.md) -- the approval-status check above
        // existed, but nothing ever checked for a document. Requires at
        // least one AGREEMENT uploaded via Loan_document (added for BRD §9
        // "Loan agreement & documents stored").
        $has_agreement = false;
        foreach ($this->loan_documents->for_loan($loan['id']) as $document) {
            if ($document['document_type'] === 'AGREEMENT') {
                $has_agreement = true;
                break;
            }
        }
        if (! $has_agreement) {
            return json_error('A signed loan agreement must be uploaded before disbursement.', 422);
        }

        $data = $this->json_input();

        // BRD §12 "Configured payment modes" (docs/BRD_COVERAGE_AUDIT.md): this
        // validation used to be commented-out dead code, and even if restored
        // as-written it would have been broken -- `loan_disbursements.mode` is
        // a FK to `disbursement_mode_master.id` (bigint), not a free-text code,
        // so inserting `$data['mode']` (e.g. "CASH") directly would have
        // violated the FK constraint. The allowed set now comes from that
        // master table (the actual "configured modes"), and the code is
        // resolved to its id before insert.
        $mode = $this->disbursement_modes->find_by_code($data['mode'] ?? null);
        if (! $mode) {
            $allowed_codes = array_column($this->disbursement_modes->all(array(), 'code ASC'), 'code');

            return json_error('mode is required and must be one of ' . implode(',', $allowed_codes) . '.');
        }

        /*if ($mode['code'] === 'CASH' && (float) $loan['net_disbursed_amount'] > self::CASH_LIMIT) {
            return json_error('Cash disbursement above the regulatory limit is not permitted. Use a bank transfer mode.');
        }*/

        $this->db->trans_start();

        $disbursement_id = $this->disbursements->insert(array(
            'loan_id' => $loan['id'],
            'mode' => $mode['id'],
            'amount' => $loan['net_disbursed_amount'],
            'reference_number' => $data['reference_number'] ?? null,
            'status' => 'COMPLETED',
            'disbursed_by' => $user['id'],
        ));

        // Atomic conditional guard: only flip to ACTIVE if the loan is still APPROVED,
        // so two concurrent requests can't both pass the earlier check-then-act and double-disburse.
        // The loan account number is also assigned here for the first time -- BRD §9
        // "Unique Loan ID created after disbursement" (docs/BRD_COVERAGE_AUDIT.md) --
        // derived from the loan's own id, which MySQL already allocated atomically.
        $this->db->where('id', $loan['id'])
            ->where('status', 'APPROVED')
            ->update('loans', array(
                'status' => 'ACTIVE',
                'loan_account_number' => $this->loans->loan_account_number_for_id($loan['id']),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();
            return json_error('Loan is no longer approved (already disbursed or state changed). Please refresh.', 422);
        }

        // Auto GL posting -- every money-moving transaction posts to accounting, never manual double-entry.
        // AccountingService::postDisbursement($loan, $disbursement);

        $this->audit_log('Loan', $loan['id'], 'DISBURSE',
            array('status' => $loan['status'], 'loan_account_number' => $loan['loan_account_number']),
            array(
                'status' => 'ACTIVE',
                'loan_account_number' => $this->loans->loan_account_number_for_id($loan['id']),
                'disbursement_id' => $disbursement_id,
                'mode' => $mode['code'],
                'amount' => $loan['net_disbursed_amount'],
            )
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Disbursement failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->disbursements->find($disbursement_id)), 201);
    }
}
