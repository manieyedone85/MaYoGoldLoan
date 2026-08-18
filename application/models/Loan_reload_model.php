<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_reload_model extends MY_Model
{
    protected $table = 'loan_reloads';

    /** Re-loan history joined with loan/customer, newest first -- for the admin Part Payments list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('loan_reloads.*, loans.loan_account_number, customers.name AS customer_name')
            ->from('loan_reloads')
            ->join('loans', 'loans.id = loan_reloads.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->order_by('loan_reloads.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
