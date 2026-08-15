<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_charge_model extends MY_Model
{
    protected $table = 'loan_charges';

    public function for_loan($loan_id)
    {
        return $this->all(array('loan_id' => $loan_id));
    }
}
