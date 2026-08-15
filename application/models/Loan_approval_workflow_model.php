<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_approval_workflow_model extends MY_Model
{
    protected $table = 'loan_approval_workflows';

    public function for_loan($loan_id)
    {
        return $this->first(array('loan_id' => $loan_id));
    }
}
