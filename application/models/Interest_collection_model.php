<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Interest_collection_model extends MY_Model
{
    protected $table = 'interest_collections';

    public function total_collected($loan_id)
    {
        $row = $this->db->select_sum('amount')->from($this->table)->where('loan_id', $loan_id)->get()->row_array();

        return (float) ($row['amount'] ?? 0);
    }
}
