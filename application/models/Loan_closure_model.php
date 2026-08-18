<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_closure_model extends MY_Model
{
    protected $table = 'loan_closures';

    /** Closure history joined with loan/customer, newest first -- for the admin Settlements list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('loan_closures.*, loans.loan_account_number, customers.name AS customer_name')
            ->from('loan_closures')
            ->join('loans', 'loans.id = loan_closures.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->order_by('loan_closures.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
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
