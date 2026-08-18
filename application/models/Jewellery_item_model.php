<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jewellery_item_model extends MY_Model
{
    protected $table = 'jewellery_items';

    /** Rows matching the given list of ids (used to sum eligible_amount for a loan). */
    public function find_in($ids)
    {
        if (empty($ids)) {
            return array();
        }

        return $this->db->from($this->table)->where_in('id', $ids)->get()->result_array();
    }

    public function sum_eligible_amount($ids)
    {
        $rows = $this->find_in($ids);
        $sum = 0.0;
        foreach ($rows as $row) {
            $sum += (float) $row['eligible_amount'];
        }

        return $sum;
    }

    public function mark_pledged($ids, $loan_id)
    {
        if (empty($ids)) {
            return;
        }

        $this->db->where_in('id', $ids)->update($this->table, array(
            'loan_id' => $loan_id,
            'status' => 'PLEDGED',
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Added by the Disbursement/Renewal/Topup/Interest/PartPayment/Settlement/
     * GoldRelease module owner -- used by Topup::eligibility() and
     * Settlement::settle(). Do not remove; merge on conflict.
     */

    /** All jewellery items pledged against a loan (used for topup re-valuation and settlement). */
    public function for_loan($loan_id)
    {
        return $this->all(array('loan_id' => $loan_id));
    }

    /** EVALUATED, unpledged items owned by a customer -- candidates for Topup::add_jewellery(). */
    public function evaluated_unpledged_for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id, 'status' => 'EVALUATED'));
    }

    /** Items joined with customer name and category name, optionally filtered -- for the admin Jewellery Items list. */
    public function with_relations($where = array(), $limit = 50)
    {
        $query = $this->db->select('jewellery_items.*, customers.name AS customer_name, jewellery_category_master.name AS category_name')
            ->from('jewellery_items')
            ->join('customers', 'customers.id = jewellery_items.customer_id', 'left')
            ->join('jewellery_category_master', 'jewellery_category_master.id = jewellery_items.category_id', 'left');

        if (! empty($where)) {
            $query->where($where);
        }

        return $query->order_by('jewellery_items.id', 'DESC')->limit($limit)->get()->result_array();
    }
}
