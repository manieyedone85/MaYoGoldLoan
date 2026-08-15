<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/SettlementController.php.
 * Routes:
 *   GET  /api/v1/loan/{loan}/closure-statement -- auth + device binding only
 *   POST /api/v1/loan/{loan}/settle            -- role:CASHIER,BRANCH_MANAGER
 */
class Settlement extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_closure_model', 'closures');
        $this->load->model('Interest_collection_model', 'collections');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
    }

    /** GET /api/v1/loan/{loan}/closure-statement */
    public function closure_statement($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $interest_paid = $this->collections->total_collected($loan['id']);

        return json_response(array(
            'sanctioned_amount' => $loan['sanctioned_amount'],
            'interest_paid' => $interest_paid,
            'total_payable_to_close' => $loan['sanctioned_amount'], // + any pending interest, per current schedule
        ));
    }

    /** POST /api/v1/loan/{loan}/settle */
    public function settle($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER', 'BRANCH_MANAGER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();
        if (! isset($data['total_amount_collected']) || ! is_numeric($data['total_amount_collected']) || (float) $data['total_amount_collected'] < 0) {
            return json_error('total_amount_collected is required and must be a non-negative number.');
        }

        $this->db->trans_start();

        $closure_id = $this->closures->insert(array(
            'loan_id' => $loan['id'],
            'total_amount_collected' => $data['total_amount_collected'],
            'closure_date' => date('Y-m-d'),
            'closed_by' => $user['id'],
        ));

        $this->loans->update($loan['id'], array('status' => 'SETTLED'));
        $this->jewellery_items->update_status_for_loan($loan['id'], 'RELEASED');

        $this->audit_log('Loan', $loan['id'], 'SETTLE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('status' => 'SETTLED', 'closure_id' => $closure_id, 'total_amount_collected' => $data['total_amount_collected'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Settlement failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->closures->find($closure_id)), 201);
    }
}
