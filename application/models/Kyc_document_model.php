<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\KycDocument (kyc_documents table).
 */
class Kyc_document_model extends MY_Model
{
    protected $table = 'kyc_document_master';

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id));
    }
}
