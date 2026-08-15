<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\CustomerAddress (customer_addresses table).
 */
class Customer_address_model extends MY_Model
{
    protected $table = 'customer_address';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
