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
}
