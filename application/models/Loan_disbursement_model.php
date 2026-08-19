<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_disbursement_model extends MY_Model
{
    protected $table = 'loan_disbursements';

    /**
     * Disbursement history joined with loan/customer/mode names, newest
     * first, paginated with optional search across loan account number /
     * customer name / customer mobile / mode code -- for the admin
     * Disbursements list.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('loan_disbursements')
                ->join('loans', 'loans.id = loan_disbursements.loan_id', 'left')
                ->join('customers', 'customers.id = loans.customer_id', 'left')
                ->join('disbursement_mode_master', 'disbursement_mode_master.id = loan_disbursements.mode', 'left');

            if ($search !== '') {
                $query->group_start()
                    ->like('loans.loan_account_number', $search)
                    ->or_like('customers.name', $search)
                    ->or_like('customers.mobile', $search)
                    ->or_like('disbursement_mode_master.code', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('loan_disbursements.*, loans.loan_account_number, customers.name AS customer_name, disbursement_mode_master.code AS mode_code, disbursement_mode_master.name AS mode_name')
            ->order_by('loan_disbursements.id', 'DESC')
            ->limit($per_page, ($page - 1) * $per_page)
            ->get()
            ->result_array();

        return array(
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
            'last_page' => (int) max(1, ceil($total / $per_page)),
        );
    }

    /** Single disbursement joined with loan/customer/branch/mode names -- for the printable disbursement receipt. */
    public function find_with_relations($id)
    {
        return $this->db->select('loan_disbursements.*, loans.loan_account_number, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name, disbursement_mode_master.code AS mode_code, disbursement_mode_master.name AS mode_name')
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->join('disbursement_mode_master', 'disbursement_mode_master.id = loan_disbursements.mode', 'left')
            ->where('loan_disbursements.id', $id)
            ->get()
            ->row_array();
    }
}
