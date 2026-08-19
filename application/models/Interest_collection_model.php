<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Interest_collection_model extends MY_Model
{
    protected $table = 'interest_collections';

    public function total_collected($loan_id)
    {
        $row = $this->db->select_sum('amount')->from($this->table)->where('loan_id', $loan_id)->get()->row_array();

        return (float) ($row['amount'] ?? 0);
    }

    /**
     * Collection history joined with loan/customer, newest first, paginated
     * with optional search across loan account number / customer name --
     * for the admin Interest Collections list.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('interest_collections')
                ->join('loans', 'loans.id = interest_collections.loan_id', 'left')
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
            ->select('interest_collections.*, loans.loan_account_number, customers.name AS customer_name')
            ->order_by('interest_collections.id', 'DESC')
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
