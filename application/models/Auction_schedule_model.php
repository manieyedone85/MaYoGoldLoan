<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auction_schedule_model extends MY_Model
{
    protected $table = 'auction_schedules';

    /** Schedules joined with branch name, newest first -- for the admin Auctions list. */
    public function with_relations()
    {
        return $this->db->select('auction_schedules.*, branches.name AS branch_name')
            ->from('auction_schedules')
            ->join('branches', 'branches.id = auction_schedules.branch_id', 'left')
            ->order_by('auction_schedules.id', 'DESC')
            ->get()
            ->result_array();
    }
}
