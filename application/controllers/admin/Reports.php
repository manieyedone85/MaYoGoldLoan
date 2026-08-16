<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Reports & KPIs (BRD §14, docs/BRD_COVERAGE_AUDIT.md).
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
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Report_model', 'reports');
    }

    /** GET /admin/reports */
    public function index()
    {
        $branch_id = $this->input->get('branch_id') !== '' && $this->input->get('branch_id') !== null
            ? (int) $this->input->get('branch_id')
            : null;
        $date = $this->input->get('date') ?: date('Y-m-d');
        $from = $this->input->get('from') ?: date('Y-m-d', strtotime('-30 days'));
        $to = $this->input->get('to') ?: date('Y-m-d');

        $this->render('reports', array(
            'page_title' => 'Reports & KPIs',
            'branches' => $this->branches->all(array(), 'name ASC'),
            'filters' => array('branch_id' => $branch_id, 'date' => $date, 'from' => $from, 'to' => $to),
            'loan_status' => $this->reports->loan_status_breakdown($branch_id),
            'overdue_emi' => $this->reports->overdue_emi($branch_id),
            'daily_cash' => $this->reports->daily_cash($branch_id, $date),
            'daily_collection' => $this->reports->daily_collection($branch_id, $date),
            'kpi_summary' => $this->reports->kpi_summary($from, $to),
            'branch_performance' => $this->reports->branch_performance($from, $to),
            'employee_performance' => $this->reports->employee_performance($from, $to),
            'renewal_topup_reloan' => $this->reports->renewal_topup_reloan($from, $to),
            'jewellery_release' => $this->reports->jewellery_release($branch_id, $from, $to),
            'audit_activity' => $this->reports->audit_activity($from, $to, null, null, 50),
        ));
    }
}
