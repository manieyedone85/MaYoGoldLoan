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
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Gold_rate_model', 'gold_rates');
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
        // Every sibling money-moving endpoint (reload, renew, settle, topup
        // approve/disburse) gates on the loan's status before touching its
        // balance -- this one didn't, so a DRAFT/SETTLED/CLOSED loan could be
        // forced back to PART_PAID and its sanctioned_amount driven negative.
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans can accept a part payment.', 422);
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

        // BRD §15 "Financial APIs prevent duplicate submissions": a plain
        // insert with no status transition to guard on, so a network retry
        // or double-tap would otherwise post the same payment twice. An
        // optional client-supplied idempotency_key makes a retried request
        // return the original record instead.
        if (! empty($data['idempotency_key'])) {
            $existing = $this->part_payments->first(array('idempotency_key' => $data['idempotency_key']));
            if ($existing) {
                return json_response(array('data' => $existing));
            }
        }

        $this->db->trans_start();

        $payment_id = $this->part_payments->insert(array(
            'loan_id' => $loan['id'],
            'principal_amount' => $principal_amount,
            'interest_amount' => $interest_amount,
            'collected_by' => $user['id'],
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ));

        if ($principal_amount > 0) {
            // Atomic decrement -- BR-013 "Duplicate financial posting must be
            // prevented" (docs/BRD_COVERAGE_AUDIT.md). The previous
            // read-then-write (`sanctioned_amount = $loan['sanctioned_amount']
            // - $principal_amount`) was a lost-update race: two concurrent
            // part-payments could both read the same starting balance, and
            // one payment's effect on the outstanding balance would silently
            // vanish even though both rows exist in loan_part_payments. A raw
            // SQL decrement is atomic at the DB level regardless of
            // concurrent requests. The status guard here (not just the check
            // above) closes the same race against a concurrent settle()/renew()
            // changing the loan's status between the earlier check and this write.
            $this->db->set('sanctioned_amount', 'sanctioned_amount - ' . number_format($principal_amount, 2, '.', ''), false)
                ->where('id', $loan['id'])
                ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
                ->update('loans', array('status' => 'PART_PAID', 'updated_at' => date('Y-m-d H:i:s')));

            if ($this->db->affected_rows() < 1) {
                $this->db->trans_rollback();
                return json_error('Loan is no longer ACTIVE/PART_PAID/RENEWED (state changed). Please refresh.', 422);
            }

            $updated_loan = $this->loans->find($loan['id']);

            $this->audit_log('Loan', $loan['id'], 'PART_PAYMENT',
                array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
                array('status' => 'PART_PAID', 'sanctioned_amount' => $updated_loan['sanctioned_amount'], 'payment_id' => $payment_id, 'principal_amount' => $principal_amount, 'interest_amount' => $interest_amount)
            );
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Part payment failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->part_payments->find($payment_id)), 201);
    }

    /**
     * POST /api/v1/loan/{loan}/reload
     *
     * BRD §11 fixes (docs/BRD_COVERAGE_AUDIT.md):
     * - "Excess re-loan uses eligible excess gold value" -- excess_amount_eligible
     *   used to be taken as pure client input; now it's recomputed server-side
     *   (same current-value-minus-outstanding formula Topup::eligibility() uses)
     *   and reload_amount is rejected if it exceeds that.
     * - "Approval & disbursement follow configured permissions" -- this had
     *   no role check at all, despite advancing more cash to the customer
     *   exactly like Topup::disburse() (role:CASHIER).
     */
    public function reload($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER', 'ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans are eligible for a re-loan.', 422);
        }

        $data = $this->json_input();

        if (! isset($data['reload_amount']) || ! is_numeric($data['reload_amount']) || (float) $data['reload_amount'] < 0) {
            return json_error('reload_amount is required and must be a non-negative number.');
        }

        $excess_amount_eligible = $this->_current_excess_amount_eligible($loan);
        if ((float) $data['reload_amount'] > $excess_amount_eligible) {
            return json_error("reload_amount exceeds the eligible excess gold value ({$excess_amount_eligible}).", 422);
        }

        $this->db->trans_start();

        $reload_id = $this->reloads->insert(array(
            'loan_id' => $loan['id'],
            'excess_amount_eligible' => $excess_amount_eligible,
            'reload_amount' => $data['reload_amount'],
            // BRD §11 "Related transactions retain historical references" --
            // pre-reload balance on the record itself, not only in audit_log.
            'previous_sanctioned_amount' => $loan['sanctioned_amount'],
            'processed_by' => $user['id'],
        ));

        // Atomic increment -- same BR-013 lost-update race as part_payment() above.
        $this->db->set('sanctioned_amount', 'sanctioned_amount + ' . number_format((float) $data['reload_amount'], 2, '.', ''), false)
            ->where('id', $loan['id'])
            ->update('loans', array('updated_at' => date('Y-m-d H:i:s')));

        $updated_loan = $this->loans->find($loan['id']);

        $this->audit_log('Loan', $loan['id'], 'RELOAD',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('sanctioned_amount' => $updated_loan['sanctioned_amount'], 'reload_id' => $reload_id, 'reload_amount' => $data['reload_amount'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Reload failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->reloads->find($reload_id)), 201);
    }

    /** Current value of jewellery pledged to this loan, at today's approved rate, minus what's already outstanding. */
    private function _current_excess_amount_eligible($loan)
    {
        $items = $this->jewellery_items->for_loan($loan['id']);

        $current_value = 0.0;
        foreach ($items as $item) {
            $latest_rate = $this->gold_rates->latest_approved($item['purity_karat']);
            if ($latest_rate) {
                $current_value += (float) $item['net_weight'] * (float) $latest_rate['rate_per_gram'] * ((float) $item['eligible_percentage'] / 100);
            }
        }

        return max(0, round($current_value - (float) $loan['sanctioned_amount'], 2));
    }
}
