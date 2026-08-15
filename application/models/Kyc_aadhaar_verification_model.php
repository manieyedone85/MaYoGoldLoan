<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\KycAadhaarVerification (kyc_aadhaar_verifications table).
 */
class Kyc_aadhaar_verification_model extends MY_Model
{
    protected $table = 'kyc_aadhaar_verifications';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
