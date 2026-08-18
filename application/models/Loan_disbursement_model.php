<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_disbursement_model extends MY_Model
{
    protected $table = 'loan_disbursements';

    /** Disbursement history joined with loan/customer/mode names, newest first -- for the admin Disbursements list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('loan_disbursements.*, loans.loan_account_number, customers.name AS customer_name, disbursement_mode_master.code AS mode_code, disbursement_mode_master.name AS mode_name')
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('disbursement_mode_master', 'disbursement_mode_master.id = loan_disbursements.mode', 'left')
            ->order_by('loan_disbursements.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    /** Single disbursement joined with loan/customer/branch/mode names -- for the printable disbursement receipt. */
    public function find_with_relations($id)
    {
        return $this->db->select('loan_disbursements.*, loans.loan_account_number, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name, disbursement_mode_master.code AS mode_code, disbursement_mode_master.name AS mode_name')
            ->from('loan_disbursements')
            ->join('loans', 'loans.id = loan_disbursements.loan_id', 'left')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->join('disbursement_mode_master', 'disbursement_mode_master.id = loan_disbursements.mode', 'left')
            ->where('loan_disbursements.id', $id)
            ->get()
            ->row_array();
    }
}
