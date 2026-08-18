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

    /** Documents joined with customer/document-type names, optionally filtered by status -- for the admin KYC queue. */
    public function with_relations($status = null, $limit = 50)
    {
        $query = $this->db->select('kyc_document_master.*, customers.name AS customer_name, customers.mobile AS customer_mobile, kyc_document_types.name AS document_type_name')
            ->from('kyc_document_master')
            ->join('customers', 'customers.id = kyc_document_master.customer_id', 'left')
            ->join('kyc_document_types', 'kyc_document_types.id = kyc_document_master.document_type_id', 'left');

        if ($status !== null) {
            $query->where('kyc_document_master.status', $status);
        }

        return $query->order_by('kyc_document_master.id', 'DESC')->limit($limit)->get()->result_array();
    }
}
