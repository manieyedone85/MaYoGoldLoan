<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Reports & KPIs (BRD §14, docs/BRD_COVERAGE_AUDIT.md).
 *
 * Originally one page rendering all 11 reports at once with a single shared
 * filter form -- reworked into a menu (index()) plus one filter-driven page
 * per report (view()), since cramming everything onto one page made it hard
 * to use and forced every report to share the same branch/date/period
 * filters even when they didn't apply. _report_tables() is the single
 * source of truth for each report's headers+rows shape, used by BOTH
 * view() (HTML table) and export() (.xlsx) so the two can never drift apart.
 *
 * Not a Laravel port -- the admin nav (`views/admin/_layout.php`) and
 * `routes.php` (`admin/reports` -> `admin/reports/index`) already both
 * referenced this page, but the controller and view never existed, so the
 * link was a dead 404. The aggregation logic itself lives in Report_model,
 * shared with the equivalent API surface (api/v1/Report.php) so the two
 * never drift apart.
 */
class Reports extends Admin_Controller
{
    /** Set by _report_tables() when $paginate is true, for view() to hand to the report_view.php pagination widget. */
    private $_pagination = null;

    /**
     * Metadata driving the report menu and each report's filter form.
     * `filters` lists which filter groups apply: 'branch' (a branch_id
     * dropdown, optional/all), 'date' (a single day), 'period' (from/to
     * range), 'entity_type' (free-text, audit log only). Keep in sync with
     * _report_tables().
     */
    private static $REPORT_DEFS = array(
        'loan_status' => array('label' => 'Loan Portfolio Status', 'description' => 'Active / closed / foreclosed loan counts and sanctioned totals.', 'filters' => array('branch')),
        'overdue_emi' => array('label' => 'Outstanding & Overdue EMI', 'description' => 'Loans past their due date, with days overdue and outstanding amount.', 'filters' => array('branch')),
        'daily_cash' => array('label' => 'Daily Cash', 'description' => 'Disbursed vs. collected cash for a single day.', 'filters' => array('branch', 'date')),
        'daily_collection' => array('label' => 'Daily Collection', 'description' => 'Interest, part-payments, and closures collected on a single day, by mode.', 'filters' => array('branch', 'date')),
        'kpi_summary' => array('label' => 'KPI Summary', 'description' => 'Processing time, KYC completion, disbursement volume, collection/overdue/renewal/repeat-customer rates.', 'filters' => array('period')),
        'branch_performance' => array('label' => 'Branch Performance', 'description' => 'Loans created and disbursed, per branch, over a period.', 'filters' => array('period')),
        'employee_performance' => array('label' => 'Employee Performance', 'description' => 'Loans created, disbursed, and collected, per employee, over a period.', 'filters' => array('period')),
        'renewal_topup_reloan' => array('label' => 'Renewal / Top-up / Re-loan Activity', 'description' => 'Renewals, top-ups, and re-loans booked over a period.', 'filters' => array('period')),
        'jewellery_release' => array('label' => 'Jewellery Release', 'description' => 'Jewellery items released back to customers over a period.', 'filters' => array('branch', 'period')),
        'gst_summary' => array('label' => 'GST Summary (Tax Filing)', 'description' => 'GST charged on loan processing fees, grouped by branch GSTIN.', 'filters' => array('branch', 'period')),
        'processing_fee_summary' => array('label' => 'Processing Fee Income', 'description' => 'Processing fee charged on loans -- company income, grouped by branch.', 'filters' => array('branch', 'period')),
        'audit_activity' => array('label' => 'Audit / User Activity', 'description' => 'Create/update/approve/reject actions logged over a period.', 'filters' => array('period', 'entity_type', 'paginated')),
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Report_model', 'reports');
    }

    /** GET /admin/reports -- menu of every report. */
    public function index()
    {
        $this->render('reports', array(
            'page_title' => 'Reports & KPIs',
            'report_defs' => self::$REPORT_DEFS,
        ));
    }

    /** GET /admin/reports/view/(report_code) -- one report, its own filter form, its own table(s). */
    public function view($code)
    {
        if (! isset(self::$REPORT_DEFS[$code])) {
            show_404();

            return;
        }

        $filters = $this->_read_filters($code);
        $this->_pagination = null;
        $tables = $this->_report_tables($code, $filters, true);

        $this->render('report_view', array(
            'page_title' => self::$REPORT_DEFS[$code]['label'],
            'code' => $code,
            'def' => self::$REPORT_DEFS[$code],
            'branches' => $this->branches->all(array(), 'name ASC'),
            'filters' => $filters,
            'tables' => $tables,
            'pagination' => $this->_pagination,
        ));
    }

    /** GET /admin/reports/export/(report_code) -- same filters as view(), streamed as .xlsx. Always the full result set, never just the current page. */
    public function export($code)
    {
        if (! isset(self::$REPORT_DEFS[$code])) {
            show_404();

            return;
        }

        $filters = $this->_read_filters($code);

        $this->load->library('xlsx_report');
        $this->xlsx_report->download($code . '_' . date('Ymd_His') . '.xlsx', $this->_report_tables($code, $filters, false));
    }

    /** Reads only the GET params relevant to this report's declared filter groups, with sane defaults. */
    private function _read_filters($code)
    {
        $needed = self::$REPORT_DEFS[$code]['filters'];
        $filters = array();

        if (in_array('branch', $needed, true)) {
            $filters['branch_id'] = $this->input->get('branch_id') !== '' && $this->input->get('branch_id') !== null
                ? (int) $this->input->get('branch_id')
                : null;
        }
        if (in_array('date', $needed, true)) {
            $filters['date'] = $this->input->get('date') ?: date('Y-m-d');
        }
        if (in_array('period', $needed, true)) {
            $filters['from'] = $this->input->get('from') ?: date('Y-m-d', strtotime('-30 days'));
            $filters['to'] = $this->input->get('to') ?: date('Y-m-d');
        }
        if (in_array('entity_type', $needed, true)) {
            $filters['entity_type'] = trim((string) $this->input->get('entity_type'));
        }
        if (in_array('paginated', $needed, true)) {
            $filters['page'] = max(1, (int) $this->input->get('page'));
        }

        return $filters;
    }

    /**
     * Maps each report code's (differently-shaped) data into headers+rows
     * table(s) -- the single source of truth shared by view() (rendered as
     * HTML) and export() (streamed as .xlsx), so what's on screen always
     * matches what's downloaded. $paginate is false for export() -- a
     * download should always be the full result set, never just the page
     * currently on screen.
     */
    private function _report_tables($code, array $filters, $paginate = true)
    {
        $branch_id = $filters['branch_id'] ?? null;
        $date = $filters['date'] ?? date('Y-m-d');
        $from = $filters['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to = $filters['to'] ?? date('Y-m-d');
        $entity_type = ! empty($filters['entity_type']) ? $filters['entity_type'] : null;

        switch ($code) {
            case 'loan_status':
                $rows = array();
                foreach ($this->reports->loan_status_breakdown($branch_id) as $label => $group) {
                    $rows[] = array($label, implode(', ', $group['statuses']), $group['count'], $group['total_sanctioned_amount']);
                }

                return array(array('title' => 'Loan Status', 'headers' => array('Group', 'Statuses', 'Count', 'Total Sanctioned'), 'rows' => $rows));

            case 'overdue_emi':
                $data = $this->reports->overdue_emi($branch_id);
                $rows = array();
                foreach ($data['data'] as $r) {
                    $rows[] = array($r['loan_account_number'] ?? 'Pending disbursement', $r['customer_name'], $r['customer_mobile'], $r['branch_name'], $r['outstanding_amount'], $r['due_date'], $r['days_overdue']);
                }

                return array(array('title' => 'Overdue EMI', 'headers' => array('A/C No.', 'Customer', 'Mobile', 'Branch', 'Outstanding', 'Due Date', 'Days Overdue'), 'rows' => $rows));

            case 'daily_cash':
                $d = $this->reports->daily_cash($branch_id, $date);
                $rows = array(
                    array('Date', $d['date']),
                    array('Disbursed Amount', $d['disbursed_amount']),
                    array('Disbursed Count', $d['disbursed_count']),
                    array('Collected Amount', $d['collected_amount']),
                    array('Collected Count', $d['collected_count']),
                    array('Net Cash Movement', $d['net_cash_movement']),
                );

                return array(array('title' => 'Daily Cash', 'headers' => array('Metric', 'Value'), 'rows' => $rows));

            case 'daily_collection':
                $d = $this->reports->daily_collection($branch_id, $date);
                $summary_rows = array(
                    array('Interest Collected', $d['interest_collected']['total']),
                    array('Part Payments Collected', $d['part_payments_collected']['total']),
                    array('Part Payments Count', $d['part_payments_collected']['count']),
                    array('Closures Collected', $d['closures_collected']['total']),
                    array('Closures Count', $d['closures_collected']['count']),
                    array('Grand Total Collected', $d['grand_total_collected']),
                );
                $mode_rows = array();
                foreach ($d['interest_collected']['by_mode'] as $m) {
                    $mode_rows[] = array($m['mode'], $m['total'], $m['count']);
                }

                return array(
                    array('title' => 'Daily Collection', 'headers' => array('Metric', 'Value'), 'rows' => $summary_rows),
                    array('title' => 'By Mode', 'headers' => array('Mode', 'Amount', 'Count'), 'rows' => $mode_rows),
                );

            case 'kpi_summary':
                $k = $this->reports->kpi_summary($from, $to);
                $rows = array(
                    array('Avg. Processing Time (hrs)', $k['avg_processing_time_hours']),
                    array('KYC Completion Rate (%)', $k['kyc_completion_rate_pct']),
                    array('Disbursement Volume', $k['disbursement_volume']),
                    array('Collection Rate (%)', $k['collection_rate_pct']),
                    array('Overdue Rate (%)', $k['overdue_rate_pct']),
                    array('Renewal Rate (%)', $k['renewal_rate_pct']),
                    array('Repeat Customer Rate (%)', $k['repeat_customer_rate_pct']),
                );

                return array(array('title' => 'KPI Summary', 'headers' => array('Metric', 'Value'), 'rows' => $rows));

            case 'branch_performance':
                $d = $this->reports->branch_performance($from, $to);
                $rows = array();
                foreach ($d['data'] as $r) {
                    $rows[] = array($r['branch_name'], $r['loans_created'], $r['total_sanctioned_amount'], $r['amount_disbursed'], $r['disbursements_count']);
                }

                return array(array('title' => 'Branch Performance', 'headers' => array('Branch', 'Loans Created', 'Total Sanctioned', 'Amount Disbursed', 'Disbursements Count'), 'rows' => $rows));

            case 'employee_performance':
                $d = $this->reports->employee_performance($from, $to);
                $rows = array();
                foreach ($d['data'] as $r) {
                    $rows[] = array($r['name'] ?? ('User #' . $r['user_id']), $r['employee_code'], $r['loans_created'], $r['amount_disbursed'], $r['disbursements_count'], $r['amount_collected'], $r['collections_count']);
                }

                return array(array('title' => 'Employee Performance', 'headers' => array('Employee', 'Code', 'Loans Created', 'Amount Disbursed', 'Disbursements Count', 'Amount Collected', 'Collections Count'), 'rows' => $rows));

            case 'renewal_topup_reloan':
                $d = $this->reports->renewal_topup_reloan($from, $to);
                $renewal_rows = array();
                foreach ($d['renewals']['data'] as $r) {
                    $renewal_rows[] = array($r['loan_account_number'], $r['renewed_tenure_months'], $r['interest_paid'], $r['renewal_charges'], $r['new_due_date'], $r['previous_due_date']);
                }
                $topup_rows = array();
                foreach ($d['topups']['data'] as $r) {
                    $topup_rows[] = array($r['loan_account_number'], $r['eligible_topup_amount'], $r['approved_amount'], $r['status']);
                }
                $reload_rows = array();
                foreach ($d['reloads']['data'] as $r) {
                    $reload_rows[] = array($r['loan_account_number'], $r['excess_amount_eligible'], $r['reload_amount'], $r['previous_sanctioned_amount']);
                }

                return array(
                    array('title' => 'Renewals', 'headers' => array('Loan A/C', 'Tenure (mo)', 'Interest Paid', 'Charges', 'New Due Date', 'Previous Due Date'), 'rows' => $renewal_rows),
                    array('title' => 'Top-ups', 'headers' => array('Loan A/C', 'Eligible Amount', 'Approved Amount', 'Status'), 'rows' => $topup_rows),
                    array('title' => 'Re-loans', 'headers' => array('Loan A/C', 'Excess Eligible', 'Reload Amount', 'Previous Sanctioned'), 'rows' => $reload_rows),
                );

            case 'jewellery_release':
                $d = $this->reports->jewellery_release($branch_id, $from, $to);
                $rows = array();
                foreach ($d['data'] as $r) {
                    $rows[] = array($r['loan_account_number'] ?? 'Pending disbursement', $r['customer_name'], $r['barcode'], $r['released_to'], $r['released_at']);
                }

                return array(array('title' => 'Jewellery Release', 'headers' => array('Loan A/C', 'Customer', 'Barcode', 'Released To', 'Released At'), 'rows' => $rows));

            case 'audit_activity':
                $page = $paginate ? max(1, (int) ($filters['page'] ?? 1)) : 1;
                $per_page = $paginate ? 50 : 500;
                $d = $this->reports->audit_activity($from, $to, null, $entity_type, $per_page, $page);
                if ($paginate) {
                    $this->_pagination = array(
                        'page' => $d['page'],
                        'last_page' => $d['last_page'],
                        'total' => $d['total'],
                        'per_page' => $d['per_page'],
                    );
                }
                $rows = array();
                foreach ($d['data'] as $r) {
                    $rows[] = array(
                        $r['created_at'], $r['entity_type'], $r['entity_id'], $r['action'], $r['actor_id'],
                        $r['before_value'] !== null ? json_encode($r['before_value']) : '',
                        $r['after_value'] !== null ? json_encode($r['after_value']) : '',
                    );
                }

                return array(array('title' => 'Audit Activity', 'headers' => array('When', 'Entity Type', 'Entity ID', 'Action', 'Actor ID', 'Before', 'After'), 'rows' => $rows));

            case 'gst_summary':
                $d = $this->reports->gst_summary($branch_id, $from, $to);
                $summary_rows = array();
                foreach ($d['by_branch'] as $b) {
                    $summary_rows[] = array($b['branch_name'], $b['gst_number'], $b['count'], $b['total_gst_amount']);
                }
                $detail_rows = array();
                foreach ($d['data'] as $r) {
                    $detail_rows[] = array($r['created_at'], $r['loan_account_number'] ?? 'Pending disbursement', $r['customer_name'], $r['branch_name'], $r['gst_number'], $r['gst_amount']);
                }

                return array(
                    array('title' => 'GST by Branch', 'headers' => array('Branch', 'GSTIN', 'Count', 'Total GST'), 'rows' => $summary_rows),
                    array('title' => 'GST Detail', 'headers' => array('Date', 'Loan A/C', 'Customer', 'Branch', 'GSTIN', 'GST Amount'), 'rows' => $detail_rows),
                );

            case 'processing_fee_summary':
                $d = $this->reports->processing_fee_summary($branch_id, $from, $to);
                $summary_rows = array();
                foreach ($d['by_branch'] as $b) {
                    $summary_rows[] = array($b['branch_name'], $b['count'], $b['total_processing_fee_amount']);
                }
                $detail_rows = array();
                foreach ($d['data'] as $r) {
                    $detail_rows[] = array($r['created_at'], $r['loan_account_number'] ?? 'Pending disbursement', $r['customer_name'], $r['branch_name'], $r['processing_fee_amount']);
                }

                return array(
                    array('title' => 'Processing Fee Income by Branch', 'headers' => array('Branch', 'Count', 'Total Processing Fee'), 'rows' => $summary_rows),
                    array('title' => 'Processing Fee Detail', 'headers' => array('Date', 'Loan A/C', 'Customer', 'Branch', 'Processing Fee Amount'), 'rows' => $detail_rows),
                );

            default:
                return array();
        }
    }
}
