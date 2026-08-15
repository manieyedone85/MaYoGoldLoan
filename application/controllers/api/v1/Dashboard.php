<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API dashboard controller — mirrors app/Http/Controllers/Api/V1/DashboardController.php.
 * (Distinct from application/controllers/admin/Dashboard.php, the admin panel one —
 * not touched here.)
 * GET /api/v1/dashboard/summary?branchId=&date=
 */
class Dashboard extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_device_binding();
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Loan_model', 'loans');
    }

    public function summary()
    {
        $branch_id = $this->input->get('branchId');
        $date = $this->input->get('date');

        if (empty($branch_id) || ! $this->branches->find($branch_id)) {
            return json_error('branchId is required and must reference an existing branch.');
        }

        $date = $date ?: date('Y-m-d');

        $todays_loans = $this->loans->count(array(
            'branch_id' => $branch_id,
            'DATE(loan_date)' => $date,
        ));

        $todays_collection = $this->db->select_sum('interest_collections.amount', 'total')
            ->from('interest_collections')
            ->join('loans', 'loans.id = interest_collections.loan_id')
            ->where('loans.branch_id', $branch_id)
            ->where('DATE(interest_collections.created_at)', $date)
            ->get()->row_array();

        $todays_disbursement = $this->db->select_sum('loan_disbursements.amount', 'total')
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id')
            ->where('loans.branch_id', $branch_id)
            ->where('DATE(loan_disbursements.created_at)', $date)
            ->get()->row_array();

        $pending_approval = $this->loans->count(array(
            'branch_id' => $branch_id,
            'status' => 'PENDING_APPROVAL',
        ));

        $active_portfolio = $this->db->select_sum('sanctioned_amount', 'total')
            ->from('loans')
            ->where('branch_id', $branch_id)
            ->where('status', 'ACTIVE')
            ->get()->row_array();

        return json_response(array(
            'todays_loans' => $todays_loans,
            'todays_collection' => (float) ($todays_collection['total'] ?? 0),
            'todays_disbursement' => (float) ($todays_disbursement['total'] ?? 0),
            'pending_approval' => $pending_approval,
            'active_portfolio' => (float) ($active_portfolio['total'] ?? 0),
        ));
    }
}
