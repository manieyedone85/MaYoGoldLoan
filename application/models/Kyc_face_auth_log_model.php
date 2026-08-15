<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\KycFaceAuthLog (kyc_face_auth_logs table).
 */
class Kyc_face_auth_log_model extends MY_Model
{
    protected $table = 'kyc_face_auth_logs';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
