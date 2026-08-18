<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_part_payment_model extends MY_Model
{
    protected $table = 'loan_part_payments';

    /** Part-payment history joined with loan/customer, newest first -- for the admin Part Payments list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('loan_part_payments.*, loans.loan_account_number, customers.name AS customer_name')
            ->from('loan_part_payments')
            ->join('loans', 'loans.id = loan_part_payments.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->order_by('loan_part_payments.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
