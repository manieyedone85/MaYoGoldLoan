<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/TopupController.php.
 * Routes:
 *   GET  /api/v1/loan/{loan}/topup/eligibility     -- auth + device binding only
 *   POST /api/v1/loan/{loan}/topup/approve         -- role:BRANCH_MANAGER,REGIONAL_MANAGER
 *   POST /api/v1/loan/{loan}/topup/disburse        -- role:CASHIER
 */
class Topup extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_topup_model', 'topups');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Gold_rate_model', 'gold_rates');
    }

    /** GET /api/v1/loan/{loan}/topup/eligibility */
    public function eligibility($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        return json_response(array('eligible_topup_amount' => $this->_current_eligible_topup($loan)));
    }

    /**
     * POST /api/v1/loan/{loan}/topup/add-jewellery  (role: APPRAISER, BRANCH_MANAGER)
     *
     * Not a Laravel port -- added for BRD §11 "Additional jewellery may be
     * added" (docs/BRD_COVERAGE_AUDIT.md), which was entirely Missing: the
     * only way jewellery got linked to a loan was at Loan::store() time.
     * Pledges already-evaluated, unpledged items belonging to the same
     * customer onto this loan, raising the loan's eligible_amount (the
     * collateral ceiling) -- but NOT sanctioned_amount, since actually
     * advancing more cash still has to go through approve()/disburse() like
     * any other top-up.
     */
    public function add_jewellery($loan_id)
    {
        $this->require_auth();
        $this->require_role(array('APPRAISER', 'BRANCH_MANAGER', 'ADMIN'));
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans can have jewellery added.', 422);
        }

        $data = $this->json_input();
        if (empty($data['jewellery_item_ids']) || ! is_array($data['jewellery_item_ids'])) {
            return json_error('jewellery_item_ids is required and must be an array with at least one item.');
        }

        $items = $this->jewellery_items->find_in($data['jewellery_item_ids']);
        if (count($items) !== count($data['jewellery_item_ids'])) {
            return json_error('jewellery_item_ids contains an id that does not exist.');
        }

        $added_eligible = 0.0;
        foreach ($items as $item) {
            if ($item['status'] !== 'EVALUATED') {
                return json_error("Jewellery item {$item['id']} is not available to pledge (status {$item['status']}).", 422);
            }
            if ((int) $item['customer_id'] !== (int) $loan['customer_id']) {
                return json_error("Jewellery item {$item['id']} does not belong to this loan's customer.", 422);
            }
            $added_eligible += (float) $item['eligible_amount'];
        }

        $this->db->trans_start();

        $this->jewellery_items->mark_pledged($data['jewellery_item_ids'], $loan_id);

        $new_eligible_amount = (float) $loan['eligible_amount'] + $added_eligible;
        $this->loans->update($loan_id, array('eligible_amount' => $new_eligible_amount));

        $this->audit_log('Loan', $loan_id, 'TOPUP_ADD_JEWELLERY',
            array('eligible_amount' => $loan['eligible_amount']),
            array('eligible_amount' => $new_eligible_amount, 'jewellery_item_ids' => $data['jewellery_item_ids'], 'added_eligible_amount' => $added_eligible)
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Unable to add jewellery. Please retry.', 500);
        }

        return json_response(array('data' => $this->loans->find($loan_id)));
    }

    /**
     * POST /api/v1/loan/{loan}/topup/approve
     *
     * BRD §11 "Interest/charges/revised amounts shown before confirmation":
     * approved_amount used to be taken as pure client input and even echoed
     * straight into `eligible_topup_amount` (i.e. it never actually recorded
     * what the server thought the ceiling was). Now the ceiling is
     * recomputed server-side -- the same formula eligibility() previews --
     * and the request is rejected if it's exceeded.
     */
    public function approve($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans are eligible for a top-up.', 422);
        }

        $data = $this->json_input();
        if (! isset($data['approved_amount']) || ! is_numeric($data['approved_amount']) || (float) $data['approved_amount'] < 0) {
            return json_error('approved_amount is required and must be a non-negative number.');
        }

        $eligible_topup_amount = $this->_current_eligible_topup($loan);
        if ((float) $data['approved_amount'] > $eligible_topup_amount) {
            return json_error("approved_amount exceeds the eligible top-up amount ({$eligible_topup_amount}).", 422);
        }

        $topup_id = $this->topups->insert(array(
            'loan_id' => $loan['id'],
            'eligible_topup_amount' => $eligible_topup_amount,
            'approved_amount' => $data['approved_amount'],
            'status' => 'APPROVED',
            'approved_by' => $user['id'],
        ));

        $this->audit_log('Loan', $loan['id'], 'TOPUP_APPROVE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('topup_id' => $topup_id, 'eligible_topup_amount' => $eligible_topup_amount, 'approved_amount' => $data['approved_amount'])
        );

        return json_response(array('data' => $this->topups->find($topup_id)), 201);
    }

    /** POST /api/v1/loan/{loan}/topup/disburse */
    public function disburse($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }
        // approve()/add_jewellery() both gate on the loan's own status, but
        // disburse() only ever checked the topup row's status -- a loan
        // settled/closed after approval but before disbursement could still
        // have cash paid out against it. Guarded again below at the moment of
        // the actual balance write, to close the race against a concurrent
        // settle() between this check and that write.
        if (! in_array($loan['status'], array('ACTIVE', 'PART_PAID', 'RENEWED'), true)) {
            return json_error('Only ACTIVE, PART_PAID, or RENEWED loans can receive a top-up disbursement.', 422);
        }

        $topup = $this->topups->latest_approved($loan['id']);
        if (! $topup) {
            return json_error('No approved topup found for this loan.', 404);
        }

        $this->db->trans_start();

        // Atomic conditional guard -- BR-013 "Duplicate financial posting
        // must be prevented" (docs/BRD_COVERAGE_AUDIT.md). The previous blind
        // update (no WHERE status='APPROVED') meant two concurrent disburse()
        // requests could both pass the earlier check-then-act and both add
        // approved_amount to sanctioned_amount -- double-disbursing the same
        // topup. Same pattern already used by Disbursement::disburse().
        $this->db->where('id', $topup['id'])
            ->where('status', 'APPROVED')
            ->update('loan_topups', array(
                'status' => 'DISBURSED',
                // BRD §11 "Related transactions retain historical references" --
                // the pre-disbursement balance is now on the topup record itself,
                // not only inside audit_log's JSON blob.
                'previous_sanctioned_amount' => $loan['sanctioned_amount'],
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();
            return json_error('This topup has already been disbursed or is no longer approved. Please refresh.', 422);
        }

        // Atomic increment -- avoids the same lost-update race independently
        // of the guard above (two different topups on the same loan could
        // otherwise still clobber each other's balance change). Also
        // re-checks loan status here, closing the race against a concurrent
        // settle() landing between the earlier status check and this write.
        $this->db->set('sanctioned_amount', 'sanctioned_amount + ' . number_format((float) $topup['approved_amount'], 2, '.', ''), false)
            ->where('id', $loan['id'])
            ->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->update('loans');

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();
            return json_error('Loan is no longer ACTIVE/PART_PAID/RENEWED (state changed). Please refresh.', 422);
        }

        $updated_loan = $this->loans->find($loan['id']);

        $this->audit_log('Loan', $loan['id'], 'TOPUP_DISBURSE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('sanctioned_amount' => $updated_loan['sanctioned_amount'], 'topup_id' => $topup['id'], 'approved_amount' => $topup['approved_amount'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Topup disbursement failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->topups->find($topup['id'])));
    }

    /** Current value of jewellery pledged to this loan, at today's approved rate, minus what's already outstanding. */
    private function _current_eligible_topup($loan)
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
