<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_reload_model extends MY_Model
{
    protected $table = 'loan_reloads';

    /**
     * Re-loan history joined with loan/customer, newest first, paginated
     * with optional search across loan account number / customer name --
     * for the admin Part Payments list.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('loan_reloads')
                ->join('loans', 'loans.id = loan_reloads.loan_id', 'left')
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
            ->select('loan_reloads.*, loans.loan_account_number, customers.name AS customer_name')
            ->order_by('loan_reloads.id', 'DESC')
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
}
