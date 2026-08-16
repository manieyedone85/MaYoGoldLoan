<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/ReportController.php — single generic
 * parameterized endpoint (GET /api/v1/reports/{reportCode}) rather than one
 * route per report. DAILY_CASH and NPA were the only two implemented in the
 * Laravel source, and DAILY_CASH was a stub there too.
 *
 * Every other report code below is NOT a Laravel port -- added for BRD §14
 * "Reports & KPIs" (docs/BRD_COVERAGE_AUDIT.md), which the original audit
 * flagged as essentially unbuilt (7 of 9 requirements had zero
 * implementation). The aggregation logic lives in Report_model, shared with
 * admin/Reports.php (the admin nav already links to `admin/reports`, but
 * that controller/view never existed -- a dead 404 -- fixed alongside this).
 */
class Report extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_device_binding();
        // Every report code here (including the two pre-existing ones,
        // DAILY_CASH/NPA) can return cross-branch financial data, borrower
        // PII (OVERDUE_EMI), or raw audit_logs rows (AUDIT_ACTIVITY) --
        // this had no role check at all, so any authenticated user of any
        // role, at any branch, could read every report for every branch.
        $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS', 'ADMIN'));
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Report_model', 'reports');
    }

    /** GET /api/v1/reports/(:any) */
    public function generate($report_code)
    {
        switch ($report_code) {
            case 'DAILY_CASH':
                return $this->daily_cash();
            case 'NPA':
                return $this->npa();
            case 'LOAN_STATUS':
                return json_response(array('report' => 'LOAN_STATUS', 'data' => $this->reports->loan_status_breakdown($this->_branch_id())));
            case 'OVERDUE_EMI':
                return json_response(array_merge(array('report' => 'OVERDUE_EMI'), $this->reports->overdue_emi($this->_branch_id())));
            case 'DAILY_COLLECTION':
                return $this->daily_collection();
            case 'BRANCH_PERFORMANCE':
                return json_response(array_merge(array('report' => 'BRANCH_PERFORMANCE'), $this->reports->branch_performance($this->input->get('from'), $this->input->get('to'))));
            case 'EMPLOYEE_PERFORMANCE':
                return json_response(array_merge(array('report' => 'EMPLOYEE_PERFORMANCE'), $this->reports->employee_performance($this->input->get('from'), $this->input->get('to'))));
            case 'JEWELLERY_RELEASE':
                return json_response(array_merge(array('report' => 'JEWELLERY_RELEASE'), $this->reports->jewellery_release($this->_branch_id(), $this->input->get('from'), $this->input->get('to'))));
            case 'RENEWAL_TOPUP_RELOAN':
                return json_response(array_merge(array('report' => 'RENEWAL_TOPUP_RELOAN'), $this->reports->renewal_topup_reloan($this->input->get('from'), $this->input->get('to'))));
            case 'AUDIT_ACTIVITY':
                return json_response(array_merge(array('report' => 'AUDIT_ACTIVITY'), $this->reports->audit_activity(
                    $this->input->get('from'),
                    $this->input->get('to'),
                    $this->input->get('actorId'),
                    $this->input->get('entityType')
                )));
            case 'KPI_SUMMARY':
                return json_response(array_merge(array('report' => 'KPI_SUMMARY'), $this->reports->kpi_summary($this->input->get('from'), $this->input->get('to'))));
        }

        return json_error('Unknown or not-yet-implemented report code.', 404);
    }

    private function daily_cash()
    {
        return json_response(array_merge(array('report' => 'DAILY_CASH'), $this->reports->daily_cash($this->_branch_id(), $this->input->get('date'))));
    }

    private function daily_collection()
    {
        return json_response(array_merge(array('report' => 'DAILY_COLLECTION'), $this->reports->daily_collection($this->_branch_id(), $this->input->get('date'))));
    }

    private function npa()
    {
        $loans = $this->loans->with_relations(array('loans.status' => 'NPA'));

        return json_response(array('report' => 'NPA', 'data' => $loans));
    }

    private function _branch_id()
    {
        $branch_id = $this->input->get('branchId');

        return ($branch_id !== null && $branch_id !== '') ? (int) $branch_id : null;
    }
}
