<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionBid extends Model
{
    protected $fillable = ['auction_schedule_id', 'gold_packet_id', 'bidder_id', 'bid_amount'];
}
