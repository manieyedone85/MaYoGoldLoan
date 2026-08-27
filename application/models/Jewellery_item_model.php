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

    /** Inverse of mark_pledged() -- used when a loan is cancelled before disbursement so its items become available again. */
    public function release_pledge($ids)
    {
        if (empty($ids)) {
            return;
        }

        $this->db->where_in('id', $ids)->update($this->table, array(
            'loan_id' => null,
            'status' => 'EVALUATED',
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

    /**
     * Items joined with customer name and category name, optionally filtered
     * and/or searched by barcode/customer name/mobile -- for the admin
     * Jewellery Items list. Mirrors Customer_model::admin_list()'s
     * closure-rebuild pagination shape.
     */
    public function with_relations($where = array(), $search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($where, $search) {
            $query = $this->db->from('jewellery_items')
                ->join('customers', 'customers.id = jewellery_items.customer_id', 'left')
                ->join('jewellery_category_master', 'jewellery_category_master.id = jewellery_items.category_id', 'left');

            if (! empty($where)) {
                $query->where($where);
            }
            if ($search !== '') {
                $query->group_start()
                    ->like('jewellery_items.barcode', $search)
                    ->or_like('customers.name', $search)
                    ->or_like('customers.mobile', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('jewellery_items.*, customers.name AS customer_name, jewellery_category_master.name AS category_name')
            ->order_by('jewellery_items.id', 'DESC')
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

    /**
     * Plain (non-paginated) items list joined with customer/category names,
     * capped at $limit -- preserves with_relations()'s pre-pagination
     * behavior for Loans::receipt(), which needs a full flat list of a
     * loan's pledged items, not a paginated page of them.
     */
    public function with_relations_limited($where = array(), $limit = 100)
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
