<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auction_bid_model extends MY_Model
{
    protected $table = 'auction_bids';

    /**
     * Highest bid for a given schedule + packet (mirrors
     * AuctionBid::where(...)->orderByDesc('bid_amount')->firstOrFail()).
     */
    public function top_bid($auction_schedule_id, $gold_packet_id)
    {
        return $this->db->from($this->table)
            ->where('auction_schedule_id', $auction_schedule_id)
            ->where('gold_packet_id', $gold_packet_id)
            ->order_by('bid_amount', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }
}
