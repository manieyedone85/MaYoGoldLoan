<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\CustomerFamilyMember (customer_family_members table).
 */
class Customer_family_member_model extends MY_Model
{
    protected $table = 'customer_family_members';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
