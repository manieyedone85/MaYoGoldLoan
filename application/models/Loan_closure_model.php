<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_closure_model extends MY_Model
{
    protected $table = 'loan_closures';

    /**
     * Closure history joined with loan/customer, newest first, paginated
     * with optional search across loan account number / customer name --
     * for the admin Settlements list.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('loan_closures')
                ->join('loans', 'loans.id = loan_closures.loan_id', 'left')
                ->join('customers', 'customers.id = loans.customer_id', 'left');

            if ($search !== '') {
                $query->group_start()
                    ->like('loans.loan_account_number', $search)
                    ->or_like('customers.name', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('loan_closures.*, loans.loan_account_number, customers.name AS customer_name')
            ->order_by('loan_closures.id', 'DESC')
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

    /** Single closure joined with loan/customer/branch -- for the printable settlement receipt. */
    public function find_with_relations($id)
    {
        return $this->db->select('loan_closures.*, loans.loan_account_number, loans.sanctioned_amount, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name')
            ->from('loan_closures')
            ->join('loans', 'loans.id = loan_closures.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->where('loan_closures.id', $id)
            ->get()
            ->row_array();
    }
}
