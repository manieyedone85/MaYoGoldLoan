<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_renewal_model extends MY_Model
{
    protected $table = 'loan_renewals';

    /** Renewal history joined with loan/customer, newest first -- for the admin Renewals list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('loan_renewals.*, loans.loan_account_number, customers.name AS customer_name')
            ->from('loan_renewals')
            ->join('loans', 'loans.id = loan_renewals.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->order_by('loan_renewals.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
