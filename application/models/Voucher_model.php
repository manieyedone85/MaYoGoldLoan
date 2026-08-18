<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher_model extends MY_Model
{
    protected $table = 'vouchers';

    public function next_voucher_number()
    {
        $max_id = (int) $this->db->select_max('id')->get($this->table)->row('id');

        return 'VCH' . date('Ymd') . str_pad((string) ($max_id + 1), 6, '0', STR_PAD_LEFT);
    }

    /** Vouchers joined with branch name, newest first -- for the admin Accounting list. */
    public function with_relations($limit = 50)
    {
        return $this->db->select('vouchers.*, branches.name AS branch_name')
            ->from('vouchers')
            ->join('branches', 'branches.id = vouchers.branch_id', 'left')
            ->order_by('vouchers.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
