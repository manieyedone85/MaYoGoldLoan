<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_approval_limit_model extends MY_Model
{
    protected $table = 'loan_approval_limits';

    public function for_role($role_id)
    {
        return $this->first(array('role_id' => $role_id));
    }
}
