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

    /**
     * Documents joined with customer/document-type names, optionally filtered
     * by status and/or a name/mobile search -- for the admin KYC queue.
     * Mirrors Customer_model::admin_list()'s closure-rebuild pagination shape.
     */
    public function with_relations($status = null, $search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($status, $search) {
            $query = $this->db->from('kyc_document_master')
                ->join('customers', 'customers.id = kyc_document_master.customer_id', 'left')
                ->join('kyc_document_types', 'kyc_document_types.id = kyc_document_master.document_type_id', 'left');

            if ($status !== null) {
                $query->where('kyc_document_master.status', $status);
            }
            if ($search !== '') {
                $query->group_start()
                    ->like('customers.name', $search)
                    ->or_like('customers.mobile', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('kyc_document_master.*, customers.name AS customer_name, customers.mobile AS customer_mobile, kyc_document_types.name AS document_type_name')
            ->order_by('kyc_document_master.id', 'DESC')
            ->limit($per_page, ($page - 1) * $per_page)
            ->get()
            ->result_array();

        return array(
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
            'last_page' => (int) max(1, ceil($total / $per_page)),
        );
    }
}
