<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gold_rate_model extends MY_Model
{
    protected $table = 'gold_rates';

    /**
     * Latest APPROVED gold rate for a given karat, ordered by effective_date
     * descending — mirrors GoldRate::where('karat', ...)->where('status',
     * 'APPROVED')->latest('effective_date')->firstOrFail() in Laravel.
     */
    public function latest_approved($karat)
    {
        return $this->db->from($this->table)
            ->where('karat', $karat)
            ->where('status', 'APPROVED')
            ->order_by('effective_date', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }
}
