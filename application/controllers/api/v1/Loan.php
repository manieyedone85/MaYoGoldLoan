<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/LoanController.php (calculate, store,
 * emiSchedule). Loan approval workflow lives in Loan_approval.php.
 */
class Loan extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Loan_charge_model', 'loan_charges');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Jewellery_image_model', 'jewellery_images');
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Loan_disbursement_model', 'loan_disbursements');
        $this->load->model('Interest_collection_model', 'interest_collections');
        $this->load->model('Loan_part_payment_model', 'loan_part_payments');
        $this->load->model('Loan_approval_workflow_model', 'loan_approval_workflows');
        $this->load->model('Loan_approval_log_model', 'loan_approval_logs');
        $this->load->model('Loan_renewal_model', 'loan_renewals');
        $this->load->model('Loan_topup_model', 'loan_topups');
        $this->load->model('Loan_reload_model', 'loan_reloads');
        $this->load->model('Loan_closure_model', 'loan_closures');
    }

    /** POST /api/v1/loan/calculate */
    public function calculate()
    {
        $this->require_auth();

        $data = $this->json_input();
        $error = $this->_validate_calculate_input($data);
        if ($error) {
            return json_error($error);
        }

        $product = $this->loan_products->find($data['loan_product_id']);
        $eligibleAmount = $this->jewellery_items->sum_eligible_amount($data['jewellery_item_ids']);

        $processingFee = round($eligibleAmount * ($product['processing_fee_pct'] / 100), 2);
        $gst = round($processingFee * ($product['gst_pct'] / 100), 2);
        $insurance = round($eligibleAmount * ($product['insurance_pct'] / 100), 2);
        $netDisbursed = $eligibleAmount - $processingFee - $gst - $insurance;

        return json_response(array(
            'eligible_amount' => $eligibleAmount,
            'interest_rate_pct' => $product['interest_rate_pct'],
            'interest_type' => $product['interest_type'],
            'tenure_months' => $product['tenure_months'],
            'processing_fee' => $processingFee,
            'gst_amount' => $gst,
            'insurance_amount' => $insurance,
            'net_disbursed_amount' => $netDisbursed,
        ));
    }

    /** POST /api/v1/loan  (create in DRAFT, then submit-for-approval) */
    public function store()
    {
        $user = $this->require_auth();

        $data = $this->json_input();
        $error = $this->_validate_calculate_input($data);
        if ($error) {
            return json_error($error);
        }

        $product = $this->loan_products->find($data['loan_product_id']);
        $eligibleAmount = $this->jewellery_items->sum_eligible_amount($data['jewellery_item_ids']);

        $processingFee = round($eligibleAmount * ($product['processing_fee_pct'] / 100), 2);
        $gst = round($processingFee * ($product['gst_pct'] / 100), 2);
        $insurance = round($eligibleAmount * ($product['insurance_pct'] / 100), 2);
        $netDisbursed = $eligibleAmount - $processingFee - $gst - $insurance;

        $this->db->trans_start();

        $loanId = $this->loans->insert(array(
            'loan_account_number' => $this->loans->next_loan_account_number(),
            'customer_id' => $data['customer_id'],
            'branch_id' => $data['branch_id'],
            'loan_product_id' => $product['id'],
            'eligible_amount' => $eligibleAmount,
            'sanctioned_amount' => $eligibleAmount,
            'interest_rate_pct' => $product['interest_rate_pct'],
            'processing_fee' => $processingFee,
            'gst_amount' => $gst,
            'insurance_amount' => $insurance,
            'net_disbursed_amount' => $netDisbursed,
            'loan_date' => date('Y-m-d'),
            'due_date' => $this->_add_months(date('Y-m-d'), (int) $product['tenure_months']),
            'status' => 'DRAFT',
            'created_by' => $user['id'],
        ));

        foreach (array(
            array('charge_type' => 'PROCESSING_FEE', 'amount' => $processingFee),
            array('charge_type' => 'GST', 'amount' => $gst),
            array('charge_type' => 'INSURANCE', 'amount' => $insurance),
        ) as $charge) {
            $charge['loan_id'] = $loanId;
            $this->loan_charges->insert($charge);
        }

        $this->jewellery_items->mark_pledged($data['jewellery_item_ids'], $loanId);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Unable to create loan.', 500);
        }

        $loan = $this->loans->find($loanId);
        $loan['charges'] = $this->loan_charges->for_loan($loanId);

        return json_response(array('data' => $loan), 201);
    }

    /** GET /api/v1/loan/{id}/emi-schedule */
    public function emi_schedule($loan_id)
    {
        $this->require_auth();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $product = $this->loan_products->find($loan['loan_product_id']);

        return json_response(array('data' => $this->_build_emi_schedule($loan, $product)));
    }

    /**
     * GET /api/v1/loan/{id}
     * Full loan-detail bundle for the mandatory mobile-number-search scenario
     * (BRD section 10, steps 4-11): loan summary with outstanding/EMI/tenure,
     * only the jewellery pledged against THIS loan (with images), payment
     * history, EMI schedule, a merged lifecycle timeline, and a first-pass
     * eligible-actions flag set. Mirrors admin/Loans::show() but as JSON, and
     * fills in what that view is still missing (outstanding amount, EMI,
     * tenure, jewellery images/type/hallmark, part-payments, a unified
     * timeline) rather than just porting its gaps.
     */
    public function show($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find_with_relations($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $product = $this->loan_products->find($loan['loan_product_id']);
        $emi_schedule = $this->_build_emi_schedule($loan, $product);
        $emi_amount = $emi_schedule ? $emi_schedule[0]['interest_due'] : 0;

        $jewellery_items = $this->jewellery_items->for_loan($loan_id);
        $item_ids = array_column($jewellery_items, 'id');
        $images_by_item = $this->jewellery_images->for_items($item_ids);
        foreach ($jewellery_items as &$item) {
            $item['images'] = $images_by_item[$item['id']] ?? array();
        }
        unset($item);

        $disbursements = $this->loan_disbursements->all(array('loan_id' => $loan_id));
        $interest_collections = $this->interest_collections->all(array('loan_id' => $loan_id));
        $part_payments = $this->loan_part_payments->all(array('loan_id' => $loan_id));
        $renewals = $this->loan_renewals->all(array('loan_id' => $loan_id));
        $topups = $this->loan_topups->all(array('loan_id' => $loan_id));
        $reloads = $this->loan_reloads->all(array('loan_id' => $loan_id));
        $closures = $this->loan_closures->all(array('loan_id' => $loan_id));
        $approval_logs = $this->loan_approval_logs->for_loan($loan_id);

        $timeline = array();
        $timeline[] = array('type' => 'LOAN_CREATED', 'at' => $loan['created_at'], 'summary' => 'Loan ' . $loan['loan_account_number'] . ' created.');
        foreach ($approval_logs as $row) {
            $timeline[] = array('type' => 'APPROVAL_' . $row['action'], 'at' => $row['created_at'], 'summary' => $row['stage'] . ': ' . $row['action']);
        }
        foreach ($disbursements as $row) {
            $timeline[] = array('type' => 'DISBURSEMENT', 'at' => $row['created_at'], 'summary' => 'Disbursed ' . $row['amount']);
        }
        foreach ($interest_collections as $row) {
            $timeline[] = array('type' => 'INTEREST_COLLECTED', 'at' => $row['created_at'], 'summary' => 'Interest collected ' . $row['amount']);
        }
        foreach ($part_payments as $row) {
            $timeline[] = array('type' => 'PART_PAYMENT', 'at' => $row['created_at'], 'summary' => 'Part payment ' . ($row['principal_amount'] + $row['interest_amount']));
        }
        foreach ($renewals as $row) {
            $timeline[] = array('type' => 'RENEWAL', 'at' => $row['created_at'], 'summary' => 'Renewed to ' . $row['new_due_date']);
        }
        foreach ($topups as $row) {
            $timeline[] = array('type' => 'TOPUP', 'at' => $row['created_at'], 'summary' => 'Top-up ' . $row['status'] . ' for ' . $row['approved_amount']);
        }
        foreach ($reloads as $row) {
            $timeline[] = array('type' => 'RELOAN', 'at' => $row['created_at'], 'summary' => 'Re-loan of ' . $row['reload_amount']);
        }
        foreach ($closures as $row) {
            $timeline[] = array('type' => 'CLOSURE', 'at' => $row['created_at'], 'summary' => 'Settled, collected ' . $row['total_amount_collected']);
        }
        usort($timeline, function ($a, $b) {
            return strcmp((string) $a['at'], (string) $b['at']);
        });

        $active_statuses = array('ACTIVE', 'PART_PAID');
        $is_active = in_array($loan['status'], $active_statuses, true);

        return json_response(array('data' => array(
            'loan' => array_merge($loan, array(
                // sanctioned_amount already carries the running principal balance --
                // Part_payment/Topup mutate it directly, so it doubles as the
                // current outstanding amount rather than a separately stored figure.
                'outstanding_amount' => $loan['sanctioned_amount'],
                'emi_amount' => $emi_amount,
                'tenure_months' => $product ? (int) $product['tenure_months'] : null,
            )),
            'jewellery_items' => $jewellery_items,
            'payments' => array(
                'disbursements' => $disbursements,
                'interest_collections' => $interest_collections,
                'part_payments' => $part_payments,
            ),
            'emi_schedule' => $emi_schedule,
            'timeline' => $timeline,
            // First-pass, status-based only -- the dedicated eligibility
            // endpoints (Renewal::eligibility(), Topup::eligibility(),
            // Settlement::closure_statement()) remain the source of truth
            // for the actual amounts before any of these actions are taken.
            'eligible_actions' => array(
                'payment' => $is_active,
                'renew' => $is_active,
                'topup' => $is_active,
                'reloan' => $is_active,
                'foreclosure' => $is_active,
                'print' => true,
                'download' => true,
            ),
        )));
    }

    private function _build_emi_schedule($loan, $product)
    {
        $months = $product ? (int) $product['tenure_months'] : 0;
        $monthlyInterest = round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);

        $schedule = array();
        for ($m = 1; $m <= $months; $m++) {
            $schedule[] = array(
                'month' => $m,
                'due_date' => $this->_add_months($loan['loan_date'], $m),
                'interest_due' => $monthlyInterest,
            );
        }

        return $schedule;
    }

    /** @return string|null error message, or null if valid */
    private function _validate_calculate_input($data)
    {
        if (empty($data['customer_id']) || ! $this->customers->find($data['customer_id'])) {
            return 'customer_id is required and must exist.';
        }
        if (empty($data['branch_id']) || ! $this->branches->find($data['branch_id'])) {
            return 'branch_id is required and must exist.';
        }
        if (empty($data['loan_product_id']) || ! $this->loan_products->find($data['loan_product_id'])) {
            return 'loan_product_id is required and must exist.';
        }
        if (empty($data['jewellery_item_ids']) || ! is_array($data['jewellery_item_ids'])) {
            return 'jewellery_item_ids is required and must be an array with at least one item.';
        }
        foreach ($data['jewellery_item_ids'] as $item_id) {
            if (! $this->jewellery_items->find($item_id)) {
                return "jewellery_item_ids contains an id ({$item_id}) that does not exist.";
            }
        }

        return null;
    }

    private function _add_months($date, $months)
    {
        $timestamp = strtotime($date);
        $day = (int) date('j', $timestamp);
        $timestamp = mktime(0, 0, 0, (int) date('n', $timestamp) + $months, $day, (int) date('Y', $timestamp));

        return date('Y-m-d', $timestamp);
    }
}
