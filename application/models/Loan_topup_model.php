<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_topup_model extends MY_Model
{
    protected $table = 'loan_topups';

    /** Latest APPROVED topup for a loan (used by disburse()). */
    public function latest_approved($loan_id)
    {
        return $this->db->from($this->table)
            ->where('loan_id', $loan_id)
            ->where('status', 'APPROVED')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }
}
