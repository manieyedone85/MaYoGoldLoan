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

        $items = $this->jewellery_items->for_loan($loan['id']);

        $current_value = 0.0;
        foreach ($items as $item) {
            $latest_rate = $this->gold_rates->latest_approved($item['purity_karat']);
            if ($latest_rate) {
                $current_value += (float) $item['net_weight'] * (float) $latest_rate['rate_per_gram'] * ((float) $item['eligible_percentage'] / 100);
            }
        }

        $eligible_topup = max(0, round($current_value - (float) $loan['sanctioned_amount'], 2));

        return json_response(array('eligible_topup_amount' => $eligible_topup));
    }

    /** POST /api/v1/loan/{loan}/topup/approve */
    public function approve($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();
        if (! isset($data['approved_amount']) || ! is_numeric($data['approved_amount']) || (float) $data['approved_amount'] < 0) {
            return json_error('approved_amount is required and must be a non-negative number.');
        }

        $topup_id = $this->topups->insert(array(
            'loan_id' => $loan['id'],
            'eligible_topup_amount' => $data['approved_amount'],
            'approved_amount' => $data['approved_amount'],
            'status' => 'APPROVED',
            'approved_by' => $user['id'],
        ));

        $this->audit_log('Loan', $loan['id'], 'TOPUP_APPROVE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('topup_id' => $topup_id, 'approved_amount' => $data['approved_amount'])
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

        $topup = $this->topups->latest_approved($loan['id']);
        if (! $topup) {
            return json_error('No approved topup found for this loan.', 404);
        }

        $this->db->trans_start();

        $this->topups->update($topup['id'], array('status' => 'DISBURSED'));
        $new_sanctioned_amount = (float) $loan['sanctioned_amount'] + (float) $topup['approved_amount'];
        $this->loans->update($loan['id'], array(
            'sanctioned_amount' => $new_sanctioned_amount,
        ));

        $this->audit_log('Loan', $loan['id'], 'TOPUP_DISBURSE',
            array('status' => $loan['status'], 'sanctioned_amount' => $loan['sanctioned_amount']),
            array('sanctioned_amount' => $new_sanctioned_amount, 'topup_id' => $topup['id'], 'approved_amount' => $topup['approved_amount'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Topup disbursement failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->topups->find($topup['id'])));
    }
}
