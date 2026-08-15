<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/ReportController.php — single generic
 * parameterized endpoint (GET /api/v1/reports/{reportCode}) rather than one
 * route per report. Only DAILY_CASH and NPA are implemented in the Laravel
 * source; DAILY_CASH is a stub there too (echoes branch_id/date only, no
 * aggregation yet) — ported as the same stub, not invented further.
 */
class Report extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_device_binding();
        $this->load->model('Loan_model', 'loans');
    }

    /** GET /api/v1/reports/(:any) */
    public function generate($report_code)
    {
        if ($report_code === 'DAILY_CASH') {
            return $this->daily_cash();
        }

        if ($report_code === 'NPA') {
            return $this->npa();
        }

        return json_error('Unknown or not-yet-implemented report code.', 404);
    }

    private function daily_cash()
    {
        $branch_id = $this->input->get('branchId');
        $date = $this->input->get('date') ?: date('Y-m-d');

        // Same stub as the Laravel source: aggregate cash disbursements +
        // collections for the branch/date here once that logic exists there.
        return json_response(array(
            'report' => 'DAILY_CASH',
            'branch_id' => $branch_id,
            'date' => $date,
        ));
    }

    private function npa()
    {
        $loans = $this->loans->with_relations(array('loans.status' => 'NPA'));

        return json_response(array('report' => 'NPA', 'data' => $loans));
    }
}
