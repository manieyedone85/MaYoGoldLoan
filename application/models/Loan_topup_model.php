<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_topup_model extends MY_Model
{
    protected $table = 'loan_topups';

    /** Latest APPROVED topup for a loan (used by disburse()). */
    public function latest_approved($loan_id)
    {
        return $this->db->from($this->table)
            ->where('loan_id', $loan_id)
            ->where('status', 'APPROVED')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    /**
     * Top-up history joined with loan/customer, optionally filtered by
     * status, paginated with optional search across loan account number /
     * customer name -- for the admin Top-ups list.
     */
    public function with_relations($status = null, $search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($status, $search) {
            $query = $this->db->from('loan_topups')
                ->join('loans', 'loans.id = loan_topups.loan_id', 'left')
                ->join('customers', 'customers.id = loans.customer_id', 'left');

            if ($status !== null) {
                $query->where('loan_topups.status', $status);
            }

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
            ->select('loan_topups.*, loans.loan_account_number, customers.name AS customer_name')
            ->order_by('loan_topups.id', 'DESC')
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
