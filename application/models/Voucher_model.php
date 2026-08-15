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
}
