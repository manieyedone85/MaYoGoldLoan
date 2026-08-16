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
            'eligible' => in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true),
            // Monthly interest, matching Interest::due() and Settlement's
            // closure statement -- this previously omitted the / 12 and
            // quoted a full year's interest as "due" for one month elapsed.
            'interest_due' => round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2),
        ));
    }

    /**
     * POST /api/v1/loan/{loan}/renew
     *
     * BRD §11 fixes (docs/BRD_COVERAGE_AUDIT.md):
     * - "Approval & disbursement follow configured permissions" -- this had
     *   no role check at all, unlike every other money-moving action in the
     *   loan-servicing group (Interest::collect(), Part_payment::part_payment()
     *   are both role:CASHIER); added the same gate here.
     * - "Renewal available only when eligible" was previously enforced only
     *   by the separate eligibility() preview, which nothing forced the
     *   client to call -- renew() itself never checked the loan's status.
     * - "Interest/charges/revised amounts shown before confirmation" -- the
     *   caller could pass any interest_paid; now the server recomputes
     *   interest_due (the same formula eligibility() previews) and requires
     *   it be cleared before renewing.
     */
    public function renew($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER', 'ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans are eligible for renewal.', 422);
        }

        $data = $this->json_input();

        if (! isset($data['interest_paid']) || ! is_numeric($data['interest_paid']) || (float) $data['interest_paid'] < 0) {
            return json_error('interest_paid is required and must be a non-negative number.');
        }

        if (isset($data['renewal_charges']) && (! is_numeric($data['renewal_charges']) || (float) $data['renewal_charges'] < 0)) {
            return json_error('renewal_charges must be a non-negative number.');
        }

        // Monthly interest -- this previously omitted the / 12 that
        // Interest::due()/Settlement's closure statement both apply, so it
        // was demanding a full year's interest (~12x the real monthly due)
        // before allowing a renewal, blocking the feature this fix was for.
        $interest_due = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        if ((float) $data['interest_paid'] + 0.01 < $interest_due) {
            return json_error("interest_paid must cover the interest due of {$interest_due} to renew.", 422);
        }

        $product = $this->loan_products->find($loan['loan_product_id']);
        $tenure = $product ? (int) $product['tenure_months'] : 0;
        $new_due_date = date('Y-m-d', strtotime('+' . $tenure . ' months'));

        $this->db->trans_start();

        // Atomic conditional guard -- BR-013 "Duplicate financial posting
        // must be prevented" (docs/BRD_COVERAGE_AUDIT.md), same bug class
        // fixed in Part_payment/Topup::disburse/Settlement::settle: without a
        // WHERE on the loan's current status, two concurrent renew() requests
        // could both pass the earlier check-then-act and both record a
        // renewal (double-charging interest_paid against the same term).
        $this->db->where('id', $loan['id'])
            ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->update('loans', array('status' => 'RENEWED', 'due_date' => $new_due_date, 'updated_at' => date('Y-m-d H:i:s')));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();
            return json_error('Loan is no longer ACTIVE/PART_PAID/RENEWED (state changed). Please refresh.', 422);
        }

        $renewal_id = $this->renewals->insert(array(
            'loan_id' => $loan['id'],
            'renewed_tenure_months' => $tenure,
            'interest_paid' => $data['interest_paid'],
            'renewal_charges' => $data['renewal_charges'] ?? 0,
            'new_due_date' => $new_due_date,
            // Retains the pre-renewal due date on the renewal record itself --
            // BRD §11 "Related transactions retain historical references" --
            // instead of that value only ever existing in audit_log's JSON blob.
            'previous_due_date' => $loan['due_date'],
            'processed_by' => $user['id'],
        ));

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
