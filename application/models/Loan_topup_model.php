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

    /** Top-up history joined with loan/customer, optionally filtered by status -- for the admin Top-ups list. */
    public function with_relations($status = null, $limit = 50)
    {
        $query = $this->db->select('loan_topups.*, loans.loan_account_number, customers.name AS customer_name')
            ->from('loan_topups')
            ->join('loans', 'loans.id = loan_topups.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left');

        if ($status !== null) {
            $query->where('loan_topups.status', $status);
        }

        return $query->order_by('loan_topups.id', 'DESC')->limit($limit)->get()->result_array();
    }
}
