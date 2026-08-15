<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\KycPanVerification (kyc_pan_verifications table).
 */
class Kyc_pan_verification_model extends MY_Model
{
    protected $table = 'kyc_pan_verifications';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
