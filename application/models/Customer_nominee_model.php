<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\CustomerNominee (customer_nominees table).
 */
class Customer_nominee_model extends MY_Model
{
    protected $table = 'customer_nominees';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
