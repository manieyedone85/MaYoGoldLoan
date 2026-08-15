<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_ledger_model extends MY_Model
{
    protected $table = 'customer_ledgers';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id), 'created_at');
    }
}
