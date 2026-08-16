<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Not a Laravel port -- added for BRD §14 "Reports & KPIs"
 * (docs/BRD_COVERAGE_AUDIT.md), which the original audit flagged as
 * essentially unbuilt (7 of 9 requirements had zero implementation; only a
 * DAILY_CASH stub and a dashboard widget existed). Centralized here, rather
 * than written twice, because both `api/v1/Report::generate()` (mobile/API
 * surface) and `admin/Reports::index()` (the admin nav already links to
 * `admin/reports`, but that controller/view never existed -- a dead 404)
 * need the exact same aggregation logic.
 *
 * Every method takes plain scalars (not CI_Input) so it stays reusable from
 * either an API controller (parsing GET params) or an admin controller
 * (parsing GET params the same way, rendered into a view instead of JSON).
 */
class Report_model extends MY_Model
{
    /** Requirement: "Daily loan disbursement report" + "Daily collection report" share the disbursed side. */
    public function daily_cash($branch_id = null, $date = null)
    {
        $date = $this->_normalize_date($date);
        list($start, $end) = $this->_day_range($date);

        $disbursed = $this->db->select('COALESCE(SUM(loan_disbursements.amount),0) AS total, COUNT(*) AS count', false)
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id')
            ->where('loan_disbursements.status', 'COMPLETED')
            ->where('loan_disbursements.created_at >=', $start)
            ->where('loan_disbursements.created_at <', $end);
        if ($branch_id) {
            $disbursed->where('loans.branch_id', $branch_id);
        }
        $disbursed = $disbursed->get()->row_array();

        $collected = $this->db->select('COALESCE(SUM(interest_collections.amount),0) AS total, COUNT(*) AS count', false)
            ->from('interest_collections')
            ->join('loans', 'loans.id = interest_collections.loan_id')
            ->where('interest_collections.created_at >=', $start)
            ->where('interest_collections.created_at <', $end);
        if ($branch_id) {
            $collected->where('loans.branch_id', $branch_id);
        }
        $collected = $collected->get()->row_array();

        return array(
            'branch_id' => $branch_id,
            'date' => $date,
            'disbursed_amount' => (float) $disbursed['total'],
            'disbursed_count' => (int) $disbursed['count'],
            'collected_amount' => (float) $collected['total'],
            'collected_count' => (int) $collected['count'],
            'net_cash_movement' => round((float) $collected['total'] - (float) $disbursed['total'], 2),
        );
    }

    /** Requirement: "Active/closed/foreclosed loan report". */
    public function loan_status_breakdown($branch_id = null)
    {
        $groups = array(
            'ACTIVE' => array('ACTIVE', 'RENEWED', 'PART_PAID'),
            'CLOSED' => array('SETTLED', 'CLOSED'),
            'FORECLOSED' => array('AUCTION_ELIGIBLE', 'AUCTIONED', 'NPA'),
        );

        $result = array();
        foreach ($groups as $label => $statuses) {
            $query = $this->db->select('COUNT(*) AS count, COALESCE(SUM(sanctioned_amount),0) AS total_sanctioned', false)
                ->from('loans')
                ->where_in('status', $statuses);
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
            $row = $query->get()->row_array();

            $result[$label] = array(
                'statuses' => $statuses,
                'count' => (int) $row['count'],
                'total_sanctioned_amount' => (float) $row['total_sanctioned'],
            );
        }

        return $result;
    }

    /** Requirement: "Outstanding and overdue EMI report" -- interest-only (bullet) loans, so "overdue" means due_date has passed. */
    public function overdue_emi($branch_id = null)
    {
        $today = date('Y-m-d');

        $query = $this->db->select('loans.id, loans.loan_account_number, loans.customer_id, customers.name AS customer_name, customers.mobile AS customer_mobile, loans.branch_id, branches.name AS branch_name, loans.sanctioned_amount, loans.interest_rate_pct, loans.due_date')
            ->from('loans')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->where_in('loans.status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->where('loans.due_date <', $today);
        if ($branch_id) {
            $query->where('loans.branch_id', $branch_id);
        }
        $rows = $query->order_by('loans.due_date', 'ASC')->get()->result_array();

        $today_dt = new DateTime($today);
        $total_outstanding = 0.0;
        foreach ($rows as &$row) {
            $due_dt = new DateTime($row['due_date']);
            $row['days_overdue'] = (int) $today_dt->diff($due_dt)->days;
            $row['outstanding_amount'] = (float) $row['sanctioned_amount'];
            $total_outstanding += $row['outstanding_amount'];
        }
        unset($row);

        return array(
            'branch_id' => $branch_id,
            'as_of' => $today,
            'count' => count($rows),
            'total_outstanding_amount' => round($total_outstanding, 2),
            'data' => $rows,
        );
    }

    /** Requirement: "Daily collection report" -- interest + part-payments + closures collected on one day. */
    public function daily_collection($branch_id = null, $date = null)
    {
        $date = $this->_normalize_date($date);
        list($start, $end) = $this->_day_range($date);

        $interest_query = $this->db->select('interest_collections.mode, COALESCE(SUM(interest_collections.amount),0) AS total, COUNT(*) AS count', false)
            ->from('interest_collections')
            ->join('loans', 'loans.id = interest_collections.loan_id')
            ->where('interest_collections.created_at >=', $start)
            ->where('interest_collections.created_at <', $end);
        if ($branch_id) {
            $interest_query->where('loans.branch_id', $branch_id);
        }
        $by_mode = $interest_query->group_by('interest_collections.mode')->get()->result_array();
        $interest_total = 0.0;
        foreach ($by_mode as &$row) {
            $row['total'] = (float) $row['total'];
            $row['count'] = (int) $row['count'];
            $interest_total += $row['total'];
        }
        unset($row);

        $part_payment_query = $this->db->select('COALESCE(SUM(principal_amount + interest_amount),0) AS total, COUNT(*) AS count', false)
            ->from('loan_part_payments')
            ->join('loans', 'loans.id = loan_part_payments.loan_id')
            ->where('loan_part_payments.created_at >=', $start)
            ->where('loan_part_payments.created_at <', $end);
        if ($branch_id) {
            $part_payment_query->where('loans.branch_id', $branch_id);
        }
        $part_payments = $part_payment_query->get()->row_array();

        $closure_query = $this->db->select('COALESCE(SUM(total_amount_collected),0) AS total, COUNT(*) AS count', false)
            ->from('loan_closures')
            ->join('loans', 'loans.id = loan_closures.loan_id')
            ->where('loan_closures.created_at >=', $start)
            ->where('loan_closures.created_at <', $end);
        if ($branch_id) {
            $closure_query->where('loans.branch_id', $branch_id);
        }
        $closures = $closure_query->get()->row_array();

        return array(
            'branch_id' => $branch_id,
            'date' => $date,
            'interest_collected' => array('total' => round($interest_total, 2), 'by_mode' => $by_mode),
            'part_payments_collected' => array('total' => (float) $part_payments['total'], 'count' => (int) $part_payments['count']),
            'closures_collected' => array('total' => (float) $closures['total'], 'count' => (int) $closures['count']),
            'grand_total_collected' => round($interest_total + (float) $part_payments['total'] + (float) $closures['total'], 2),
        );
    }

    /** Requirement: "Branch and employee performance" (branch half). Lists every branch, zero-activity ones included. */
    public function branch_performance($from, $to)
    {
        list($from, $to, $start, $end) = $this->_period_range($from, $to);

        $branches = $this->db->from('branches')->order_by('name', 'ASC')->get()->result_array();

        $loan_stats_by_branch = array();
        foreach ($this->db->select('branch_id, COUNT(*) AS loans_created, COALESCE(SUM(sanctioned_amount),0) AS total_sanctioned', false)
            ->from('loans')
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->group_by('branch_id')
            ->get()->result_array() as $row) {
            $loan_stats_by_branch[$row['branch_id']] = $row;
        }

        $disbursement_stats_by_branch = array();
        foreach ($this->db->select('loans.branch_id, COALESCE(SUM(loan_disbursements.amount),0) AS amount_disbursed, COUNT(*) AS disbursements_count', false)
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id')
            ->where('loan_disbursements.status', 'COMPLETED')
            ->where('loan_disbursements.created_at >=', $start)->where('loan_disbursements.created_at <', $end)
            ->group_by('loans.branch_id')
            ->get()->result_array() as $row) {
            $disbursement_stats_by_branch[$row['branch_id']] = $row;
        }

        $data = array();
        foreach ($branches as $branch) {
            $loan_row = $loan_stats_by_branch[$branch['id']] ?? array('loans_created' => 0, 'total_sanctioned' => 0);
            $disb_row = $disbursement_stats_by_branch[$branch['id']] ?? array('amount_disbursed' => 0, 'disbursements_count' => 0);

            $data[] = array(
                'branch_id' => $branch['id'],
                'branch_name' => $branch['name'],
                'loans_created' => (int) $loan_row['loans_created'],
                'total_sanctioned_amount' => (float) $loan_row['total_sanctioned'],
                'amount_disbursed' => (float) $disb_row['amount_disbursed'],
                'disbursements_count' => (int) $disb_row['disbursements_count'],
            );
        }

        return array('from' => $from, 'to' => $to, 'data' => $data);
    }

    /** Requirement: "Branch and employee performance" (employee half). Only lists employees with activity in the period. */
    public function employee_performance($from, $to)
    {
        list($from, $to, $start, $end) = $this->_period_range($from, $to);

        $by_user = array();
        $ensure = function ($uid) use (&$by_user) {
            if (! isset($by_user[$uid])) {
                $by_user[$uid] = array(
                    'user_id' => $uid, 'name' => null, 'employee_code' => null,
                    'loans_created' => 0, 'amount_disbursed' => 0.0, 'disbursements_count' => 0,
                    'amount_collected' => 0.0, 'collections_count' => 0,
                );
            }
        };

        foreach ($this->db->select('created_by AS user_id, COUNT(*) AS loans_created')
            ->from('loans')->where('created_at >=', $start)->where('created_at <', $end)
            ->group_by('created_by')->get()->result_array() as $row) {
            $ensure($row['user_id']);
            $by_user[$row['user_id']]['loans_created'] = (int) $row['loans_created'];
        }

        foreach ($this->db->select('disbursed_by AS user_id, COALESCE(SUM(amount),0) AS amount_disbursed, COUNT(*) AS disbursements_count', false)
            ->from('loan_disbursements')->where('status', 'COMPLETED')
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->group_by('disbursed_by')->get()->result_array() as $row) {
            $ensure($row['user_id']);
            $by_user[$row['user_id']]['amount_disbursed'] = (float) $row['amount_disbursed'];
            $by_user[$row['user_id']]['disbursements_count'] = (int) $row['disbursements_count'];
        }

        foreach ($this->db->select('collected_by AS user_id, COALESCE(SUM(amount),0) AS amount_collected, COUNT(*) AS collections_count', false)
            ->from('interest_collections')
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->group_by('collected_by')->get()->result_array() as $row) {
            $ensure($row['user_id']);
            $by_user[$row['user_id']]['amount_collected'] = (float) $row['amount_collected'];
            $by_user[$row['user_id']]['collections_count'] = (int) $row['collections_count'];
        }

        if (! empty($by_user)) {
            $users = $this->db->from('user_master')->where_in('id', array_keys($by_user))->get()->result_array();
            foreach ($users as $user) {
                $by_user[$user['id']]['name'] = $user['name'];
                $by_user[$user['id']]['employee_code'] = $user['employee_code'];
            }
        }

        return array('from' => $from, 'to' => $to, 'data' => array_values($by_user));
    }

    /** Requirement: "Gold pledged inventory / jewellery release report" (release half -- vault holdings already covered by Inventory::vault_status()). */
    public function jewellery_release($branch_id, $from, $to)
    {
        list($from, $to, $start, $end) = $this->_period_range($from, $to);

        $query = $this->db->select('gold_releases.*, jewellery_items.barcode, jewellery_items.customer_id, customers.name AS customer_name, loans.branch_id, loans.loan_account_number')
            ->from('gold_releases')
            ->join('jewellery_items', 'jewellery_items.id = gold_releases.jewellery_item_id', 'left')
            ->join('customers', 'customers.id = jewellery_items.customer_id', 'left')
            ->join('loans', 'loans.id = gold_releases.loan_id', 'left')
            ->where('gold_releases.status', 'RELEASED')
            ->where('gold_releases.released_at >=', $start)
            ->where('gold_releases.released_at <', $end);
        if ($branch_id) {
            $query->where('loans.branch_id', $branch_id);
        }
        $rows = $query->order_by('gold_releases.released_at', 'DESC')->get()->result_array();

        return array('branch_id' => $branch_id, 'from' => $from, 'to' => $to, 'count' => count($rows), 'data' => $rows);
    }

    /** Requirement: "Renewal, top-up and re-loan report". */
    public function renewal_topup_reloan($from, $to)
    {
        list($from, $to, $start, $end) = $this->_period_range($from, $to);

        $renewals = $this->db->select('loan_renewals.*, loans.loan_account_number, loans.branch_id')
            ->from('loan_renewals')->join('loans', 'loans.id = loan_renewals.loan_id', 'left')
            ->where('loan_renewals.created_at >=', $start)->where('loan_renewals.created_at <', $end)
            ->get()->result_array();

        $topups = $this->db->select('loan_topups.*, loans.loan_account_number, loans.branch_id')
            ->from('loan_topups')->join('loans', 'loans.id = loan_topups.loan_id', 'left')
            ->where('loan_topups.created_at >=', $start)->where('loan_topups.created_at <', $end)
            ->get()->result_array();

        $reloads = $this->db->select('loan_reloads.*, loans.loan_account_number, loans.branch_id')
            ->from('loan_reloads')->join('loans', 'loans.id = loan_reloads.loan_id', 'left')
            ->where('loan_reloads.created_at >=', $start)->where('loan_reloads.created_at <', $end)
            ->get()->result_array();

        return array(
            'from' => $from,
            'to' => $to,
            'renewals' => array('count' => count($renewals), 'total_interest_paid' => round(array_sum(array_column($renewals, 'interest_paid')), 2), 'data' => $renewals),
            'topups' => array('count' => count($topups), 'total_approved_amount' => round(array_sum(array_column($topups, 'approved_amount')), 2), 'data' => $topups),
            'reloads' => array('count' => count($reloads), 'total_reload_amount' => round(array_sum(array_column($reloads, 'reload_amount')), 2), 'data' => $reloads),
        );
    }

    /** Requirement: "Audit/user activity report" -- reads audit_logs back (BR-012's table, confirmed to already exist on the live DB). */
    public function audit_activity($from, $to, $actor_id = null, $entity_type = null, $limit = 500)
    {
        list($from, $to, $start, $end) = $this->_period_range($from, $to);

        $query = $this->db->from('audit_logs')
            ->where('created_at >=', $start)
            ->where('created_at <', $end);
        if ($actor_id) {
            $query->where('actor_id', $actor_id);
        }
        if ($entity_type) {
            $query->where('entity_type', $entity_type);
        }
        $rows = $query->order_by('created_at', 'DESC')->limit($limit)->get()->result_array();

        foreach ($rows as &$row) {
            $row['before_value'] = $row['before_value'] !== null ? json_decode($row['before_value'], true) : null;
            $row['after_value'] = $row['after_value'] !== null ? json_decode($row['after_value'], true) : null;
        }
        unset($row);

        return array('from' => $from, 'to' => $to, 'count' => count($rows), 'data' => $rows);
    }

    /**
     * Requirement: "KPIs: processing time, KYC completion, disbursement
     * volume, collection rate, overdue rate, renewal rate, repeat-customer
     * rate". Some of these (collection/renewal rate) have no precise BRD
     * formula to go on and no per-period ledger to compute them exactly
     * against, so they're documented approximations using the same
     * monthly-interest formula already used by Interest::due() and
     * Settlement's closure statement -- consistent with this codebase's
     * existing precedent of labeling first-pass estimates as such (see
     * Loan::show()'s `eligible_actions`).
     */
    public function kpi_summary($from, $to)
    {
        list($from, $to, $start, $end) = $this->_period_range($from, $to);

        $processing = $this->db->select('AVG(TIMESTAMPDIFF(HOUR, loans.created_at, loan_disbursements.created_at)) AS avg_hours', false)
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id')
            ->where('loan_disbursements.status', 'COMPLETED')
            ->where('loan_disbursements.created_at >=', $start)->where('loan_disbursements.created_at <', $end)
            ->get()->row_array();
        $avg_processing_time_hours = $processing['avg_hours'] !== null ? round((float) $processing['avg_hours'], 1) : null;

        $kyc_total = $this->db->from('customers')->where('deleted_at IS NULL', null, false)
            ->where('created_at >=', $start)->where('created_at <', $end)->count_all_results();
        $kyc_verified = $this->db->from('customers')->where('deleted_at IS NULL', null, false)
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->where('kyc_status', 'VERIFIED')->count_all_results();
        $kyc_completion_rate_pct = $kyc_total > 0 ? round(($kyc_verified / $kyc_total) * 100, 2) : null;

        $volume = $this->db->select('COALESCE(SUM(amount),0) AS total', false)
            ->from('loan_disbursements')->where('status', 'COMPLETED')
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->get()->row_array();
        $disbursement_volume = (float) $volume['total'];

        $collected = $this->db->select('COALESCE(SUM(amount),0) AS total', false)
            ->from('interest_collections')
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->get()->row_array();
        $part_payment_interest = $this->db->select('COALESCE(SUM(interest_amount),0) AS total', false)
            ->from('loan_part_payments')
            ->where('created_at >=', $start)->where('created_at <', $end)
            ->get()->row_array();
        $interest_collected = (float) $collected['total'] + (float) $part_payment_interest['total'];

        $accruing_loans = $this->db->select('sanctioned_amount, interest_rate_pct')
            ->from('loans')->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->get()->result_array();
        $monthly_interest_accrual = 0.0;
        foreach ($accruing_loans as $loan) {
            $monthly_interest_accrual += round(((float) $loan['sanctioned_amount'] * (float) $loan['interest_rate_pct'] / 100) / 12, 2);
        }
        $collection_rate_pct = $monthly_interest_accrual > 0 ? round(($interest_collected / $monthly_interest_accrual) * 100, 2) : null;

        $servicing_count = $this->db->from('loans')->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))->count_all_results();
        $overdue_count = $this->db->from('loans')->where_in('status', array('ACTIVE', 'PART_PAID', 'RENEWED'))
            ->where('due_date <', date('Y-m-d'))->count_all_results();
        $overdue_rate_pct = $servicing_count > 0 ? round(($overdue_count / $servicing_count) * 100, 2) : null;

        $renewal_count = $this->db->from('loan_renewals')
            ->where('created_at >=', $start)->where('created_at <', $end)->count_all_results();
        $renewal_rate_pct = $servicing_count > 0 ? round(($renewal_count / $servicing_count) * 100, 2) : null;

        $customers_with_loans = $this->db->select('customer_id, COUNT(*) AS loan_count')
            ->from('loans')->group_by('customer_id')->get()->result_array();
        $total_customers_with_loans = count($customers_with_loans);
        $repeat_customers = 0;
        foreach ($customers_with_loans as $row) {
            if ((int) $row['loan_count'] > 1) {
                $repeat_customers++;
            }
        }
        $repeat_customer_rate_pct = $total_customers_with_loans > 0 ? round(($repeat_customers / $total_customers_with_loans) * 100, 2) : null;

        return array(
            'from' => $from,
            'to' => $to,
            'avg_processing_time_hours' => $avg_processing_time_hours,
            'kyc_completion_rate_pct' => $kyc_completion_rate_pct,
            'disbursement_volume' => $disbursement_volume,
            'collection_rate_pct' => $collection_rate_pct,
            'overdue_rate_pct' => $overdue_rate_pct,
            'renewal_rate_pct' => $renewal_rate_pct,
            'repeat_customer_rate_pct' => $repeat_customer_rate_pct,
        );
    }

    private function _normalize_date($date)
    {
        return ($date && strtotime($date) !== false) ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
    }

    private function _day_range($date)
    {
        return array($date . ' 00:00:00', date('Y-m-d', strtotime($date . ' +1 day')) . ' 00:00:00');
    }

    /**
     * Normalizes $from/$to and computes the query bounds in one place.
     * @return array{0:string,1:string,2:string,3:string} [normalized from, normalized to, range start 'Y-m-d H:i:s', range end (exclusive) 'Y-m-d H:i:s']
     */
    private function _period_range($from, $to)
    {
        $from = $this->_normalize_date($from);
        $to = $this->_normalize_date($to);

        return array($from, $to, $from . ' 00:00:00', date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00');
    }
}
