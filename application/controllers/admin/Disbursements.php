<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Disbursements. Ports
 * application/controllers/api/v1/Disbursement.php::disburse() -- same
 * preconditions (loan APPROVED, an AGREEMENT document already uploaded,
 * mode resolved against disbursement_mode_master, cash capped at
 * Disbursement::CASH_LIMIT), same atomic APPROVED->ACTIVE guard and
 * loan_account_number assignment.
 */
class Disbursements extends Admin_Controller
{
    const CASH_LIMIT = 20000;

    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('CASHIER'));

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_disbursement_model', 'disbursements');
        $this->load->model('Disbursement_mode_model', 'disbursement_modes');
        $this->load->model('Loan_document_model', 'loan_documents');
    }

    /** GET /admin/disbursements */
    public function index()
    {
        $this->render('disbursements', array(
            'page_title' => 'Disbursements',
            'pending' => $this->loans->with_relations(array('loans.status' => 'APPROVED')),
            'history' => $this->disbursements->with_relations(50),
            'modes' => $this->disbursement_modes->all(array(), 'code ASC'),
        ));
    }

    /** POST /admin/disbursements/(:num)/disburse */
    public function disburse($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        if ($loan['status'] !== 'APPROVED') {
            return $this->_fail('Loan must be approved before disbursement.');
        }

        $has_agreement = false;
        foreach ($this->loan_documents->for_loan($loan['id']) as $document) {
            if ($document['document_type'] === 'AGREEMENT') {
                $has_agreement = true;
                break;
            }
        }
        if (! $has_agreement) {
            return $this->_fail('A signed loan agreement must be uploaded (see the loan\'s Documents panel) before disbursement.');
        }

        $mode = $this->disbursement_modes->find_by_code(trim((string) $this->input->post('mode')));
        if (! $mode) {
            $allowed_codes = array_column($this->disbursement_modes->all(array(), 'code ASC'), 'code');

            return $this->_fail('A valid disbursement mode is required (' . implode(', ', $allowed_codes) . ').');
        }

        if ($mode['code'] === 'CASH' && (float) $loan['net_disbursed_amount'] > self::CASH_LIMIT) {
            return $this->_fail('Cash disbursement above ₹' . number_format(self::CASH_LIMIT) . ' is not permitted. Use a bank transfer mode.');
        }

        $reference_number = trim((string) $this->input->post('reference_number'));

        $this->db->trans_start();

        $disbursement_id = $this->disbursements->insert(array(
            'loan_id' => $loan['id'],
            'mode' => $mode['id'],
            'amount' => $loan['net_disbursed_amount'],
            'reference_number' => $reference_number !== '' ? $reference_number : null,
            'status' => 'COMPLETED',
            'disbursed_by' => $this->user['id'],
        ));

        $this->db->where('id', $loan['id'])
            ->where('status', 'APPROVED')
            ->update('loans', array(
                'status' => 'ACTIVE',
                'loan_account_number' => $this->loans->loan_account_number_for_id($loan['id']),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return $this->_fail('Loan is no longer approved (already disbursed or state changed). Please refresh.');
        }

        $this->audit_log('Loan', $loan['id'], 'DISBURSE',
            array('status' => $loan['status']),
            array('status' => 'ACTIVE', 'disbursement_id' => $disbursement_id, 'mode' => $mode['code'], 'amount' => $loan['net_disbursed_amount'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Disbursement failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' disbursed.');
        redirect('admin/disbursements');
    }

    /** GET /admin/disbursements/(:num)/receipt -- printable customer-copy receipt. */
    public function receipt($disbursement_id)
    {
        $disbursement = $this->disbursements->find_with_relations($disbursement_id);
        if (! $disbursement) {
            show_404();

            return;
        }

        $this->render('disbursement_receipt', array(
            'page_title' => 'Disbursement Receipt — ' . ($disbursement['loan_account_number'] ?? 'Loan #' . $disbursement['loan_id']),
            'disbursement' => $disbursement,
        ));
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/disbursements');
    }
}
