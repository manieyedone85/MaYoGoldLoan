<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auction_winner_model extends MY_Model
{
    protected $table = 'auction_winners';

    /** Winners for gold packets bid on within a given auction schedule -- for the admin Auctions detail page. */
    public function for_schedule($auction_schedule_id)
    {
        return $this->db->select('auction_winners.*, auction_bidders.name AS bidder_name, gold_packets.packet_code')
            ->from('auction_winners')
            ->join('auction_bidders', 'auction_bidders.id = auction_winners.bidder_id', 'left')
            ->join('gold_packets', 'gold_packets.id = auction_winners.gold_packet_id', 'left')
            ->where('auction_winners.gold_packet_id IN (SELECT gold_packet_id FROM auction_bids WHERE auction_schedule_id = ' . (int) $auction_schedule_id . ')', null, false)
            ->get()
            ->result_array();
    }
}
