<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_approval_log_model extends MY_Model
{
    protected $table = 'loan_approval_logs';

    /** Approval log entries for a loan, oldest first — used by the admin Loans show page. */
    public function for_loan($loan_id)
    {
        return $this->all(array('loan_id' => $loan_id), 'id ASC');
    }
}
