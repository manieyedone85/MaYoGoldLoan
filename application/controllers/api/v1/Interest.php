<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/InterestController.php.
 * Routes:
 *   GET  /api/v1/loan/{loan}/interest/due     -- auth + device binding only
 *   POST /api/v1/loan/{loan}/interest/collect -- role:CASHIER
 */
class Interest extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Interest_collection_model', 'collections');
    }

    /** GET /api/v1/loan/{loan}/interest/due */
    public function due($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $months_elapsed = $this->months_elapsed($loan['loan_date']);
        $monthly_interest = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        $total_paid = $this->collections->total_collected($loan['id']);

        return json_response(array(
            'interest_due' => max(0, round(($monthly_interest * $months_elapsed) - $total_paid, 2)),
        ));
    }

    /** POST /api/v1/loan/{loan}/interest/collect */
    public function collect($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('CASHIER','ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();

        if (! isset($data['amount']) || ! is_numeric($data['amount']) || (float) $data['amount'] < 0.01) {
            return json_error('amount is required and must be at least 0.01.');
        }

        if (empty($data['mode']) || ! in_array($data['mode'], array('CASH', 'ONLINE'), true)) {
            return json_error('mode is required and must be CASH or ONLINE.');
        }

        $collection_id = $this->collections->insert(array(
            'loan_id' => $loan['id'],
            'amount' => $data['amount'],
            'mode' => $data['mode'],
            'receipt_number' => 'RCPT' . strtoupper($this->random_string(10)),
            'collected_by' => $user['id'],
        ));

        return json_response(array('data' => $this->collections->find($collection_id)), 201);
    }

    /** Mirrors Carbon::now()->diffInMonths($loan->loan_date) -- whole months elapsed since loan_date. */
    private function months_elapsed($loan_date)
    {
        $start = new DateTime($loan_date);
        $now = new DateTime();
        $diff = $start->diff($now);

        return ($diff->y * 12) + $diff->m;
    }

    /** Mirrors Illuminate\Support\Str::random(10) closely enough for a receipt suffix. */
    private function random_string($length)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $out;
    }
}
