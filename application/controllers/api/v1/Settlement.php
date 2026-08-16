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
    }

    /**
     * GET /api/v1/loan/{loan}/closure-statement
     *
     * BRD §12 "Foreclosure settlement calculated per configured rules" -- this
     * used to return `sanctioned_amount` only, with a comment admitting
     * pending interest was never added. Now recomputes accrued interest the
     * same way `Interest::due()` does (months elapsed x monthly interest,
     * minus what's already been collected) and folds it into the total.
     */
    public function closure_statement($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        return json_response($this->_compute_closure_statement($loan));
    }

    /**
     * POST /api/v1/loan/{loan}/settle
     *
     * BRD §12 "Loan closure verifies all settlement conditions" -- this used
     * to mark the loan SETTLED for any non-negative `total_amount_collected`
     * without comparing it to what's actually required to close (principal +
     * accrued interest). Now recomputes the same closure statement and
     * rejects the request if the collected amount falls short.
     */
    public function settle($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER', 'BRANCH_MANAGER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans can be settled.', 422);
        }

        $data = $this->json_input();
        if (! isset($data['total_amount_collected']) || ! is_numeric($data['total_amount_collected']) || (float) $data['total_amount_collected'] < 0) {
            return json_error('total_amount_collected is required and must be a non-negative number.');
        }

        $statement = $this->_compute_closure_statement($loan);
        if ((float) $data['total_amount_collected'] + 0.01 < $statement['total_payable_to_close']) {
            return json_error("total_amount_collected is short of the required closure amount ({$statement['total_payable_to_close']}).", 422);
        }

        $this->db->trans_start();

        // Atomic conditional guard -- BR-013 "Duplicate financial posting
        // must be prevented" (docs/BRD_COVERAGE_AUDIT.md), same bug class as
        // the ones fixed in Part_payment/Topup::disburse: without a WHERE on
        // the loan's current status, two concurrent settle() requests could
        // both pass the earlier check-then-act and both record a closure.
        // Settlement only authorizes release -- it must never flip jewellery
        // straight to RELEASED itself. Gold_release::complete() is the sole
        // place that changes a jewellery_item's status to RELEASED, and it
        // now refuses to do so unless this loan's status is SETTLED/CLOSED
        // (see Gold_release.php), so physical release still requires the
        // full id-proof/signature/photo checklist on top of this.
        $this->db->where('id', $loan['id'])
            ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->update('loans', array('status' => 'SETTLED', 'updated_at' => date('Y-m-d H:i:s')));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();
            return json_error('Loan is no longer ACTIVE/PART_PAID/RENEWED (already settled or state changed). Please refresh.', 422);
        }

        $closure_id = $this->closures->insert(array(
            'loan_id' => $loan['id'],
            'total_amount_collected' => $data['total_amount_collected'],
            'closure_date' => date('Y-m-d'),
            'closed_by' => $user['id'],
        ));

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

    private function _compute_closure_statement($loan)
    {
        $interest_paid = $this->collections->total_collected($loan['id']);
        $months_elapsed = $this->_months_elapsed($loan['loan_date']);
        $monthly_interest = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        $pending_interest = max(0, round(($monthly_interest * $months_elapsed) - $interest_paid, 2));

        return array(
            'sanctioned_amount' => (float) $loan['sanctioned_amount'],
            'interest_paid' => $interest_paid,
            'pending_interest' => $pending_interest,
            'total_payable_to_close' => round((float) $loan['sanctioned_amount'] + $pending_interest, 2),
        );
    }

    /** Mirrors Carbon::now()->diffInMonths($loan->loan_date) -- same formula as Interest::months_elapsed(). */
    private function _months_elapsed($loan_date)
    {
        $start = new DateTime($loan_date);
        $now = new DateTime();
        $diff = $start->diff($now);

        return ($diff->y * 12) + $diff->m;
    }
}
