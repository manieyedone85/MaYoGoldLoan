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
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Branch_model', 'branches');
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

        // BRD §12 "Configured payment modes: Cash / UPI / Bank Transfer / Card"
        // (docs/BRD_COVERAGE_AUDIT.md) -- this only accepted CASH/ONLINE before.
        if (empty($data['mode']) || ! in_array($data['mode'], array('CASH', 'UPI', 'BANK_TRANSFER', 'CARD'), true)) {
            return json_error('mode is required and must be one of CASH, UPI, BANK_TRANSFER, CARD.');
        }

        // BRD §15 "Financial APIs prevent duplicate submissions": unlike
        // Disbursement/Part_payment/Topup/Settlement/Renewal (BR-013, §13),
        // this is a plain insert with no status transition to guard on, so a
        // network retry or double-tap would otherwise create two receipts
        // for the same payment. An optional client-supplied idempotency_key
        // makes a retried request return the original receipt instead.
        if (! empty($data['idempotency_key'])) {
            $existing = $this->collections->first(array('idempotency_key' => $data['idempotency_key']));
            if ($existing) {
                return json_response(array('data' => $existing));
            }
        }

        $collection_id = $this->collections->insert(array(
            'loan_id' => $loan['id'],
            'amount' => $data['amount'],
            'mode' => $data['mode'],
            'receipt_number' => 'RCPT' . strtoupper($this->random_string(10)),
            'collected_by' => $user['id'],
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ));

        return json_response(array('data' => $this->collections->find($collection_id)), 201);
    }

    /**
     * GET /api/v1/interest/collection/{id}/receipt
     *
     * Not a Laravel port -- added for BRD §4/§3 "Bluetooth/thermal printing"
     * (docs/BRD_COVERAGE_AUDIT.md). A CI3 REST backend can't itself drive a
     * mobile device's Bluetooth stack or a physical thermal printer -- that's
     * inherently client-side (the mobile app talks to the printer through its
     * own SDK), the same reasoning "Encrypted network communication" (BRD
     * §15) was scoped as an infra concern outside app code. What the backend
     * can do, and previously didn't at all, is hand back print-ready receipt
     * content: a structured payload plus pre-formatted 32-column lines a
     * thermal-printer SDK can send as-is, instead of the app having to
     * reimplement receipt formatting itself.
     */
    public function receipt($collection_id)
    {
        $this->require_auth();

        $collection = $this->collections->find($collection_id);
        if (! $collection) {
            return json_error('Interest collection not found.', 404);
        }

        $loan = $this->loans->find($collection['loan_id']);
        $customer = $loan ? $this->customers->find($loan['customer_id']) : null;
        $branch = $loan ? $this->branches->find($loan['branch_id']) : null;

        $lines = array(
            str_pad($branch['name'] ?? 'Gold Loan Branch', 32, ' ', STR_PAD_BOTH),
            str_repeat('-', 32),
            'Receipt No: ' . $collection['receipt_number'],
            'Date: ' . date('d-M-Y H:i', strtotime($collection['created_at'])),
            'Loan A/C: ' . ($loan['loan_account_number'] ?? 'Pending disbursement'),
            'Customer: ' . ($customer['name'] ?? '-'),
            str_repeat('-', 32),
            'Amount: Rs. ' . number_format($collection['amount'], 2),
            'Mode: ' . $collection['mode'],
            str_repeat('-', 32),
            str_pad('Thank you', 32, ' ', STR_PAD_BOTH),
        );

        return json_response(array('data' => array(
            'receipt_number' => $collection['receipt_number'],
            'amount' => (float) $collection['amount'],
            'mode' => $collection['mode'],
            'collected_at' => $collection['created_at'],
            'loan_account_number' => $loan['loan_account_number'] ?? null,
            'customer_name' => $customer['name'] ?? null,
            'branch_name' => $branch['name'] ?? null,
            'print_lines' => $lines,
        )));
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
