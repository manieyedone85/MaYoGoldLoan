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

    /** Collection history joined with loan/customer, newest first -- for the admin Interest Collections list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('interest_collections.*, loans.loan_account_number, customers.name AS customer_name')
            ->from('interest_collections')
            ->join('loans', 'loans.id = interest_collections.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->order_by('interest_collections.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
