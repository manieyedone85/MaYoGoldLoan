<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\CustomerBiometric (customer_biometrics table). No
 * Laravel Api\V1 controller currently exposes an endpoint for this table
 * (no route in routes/api.php references it) — model provided per the
 * schema/deliverables list for future use.
 */
class Customer_biometric_model extends MY_Model
{
    protected $table = 'customer_biometrics';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
