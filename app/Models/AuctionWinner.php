<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionWinner extends Model
{
    protected $fillable = ['gold_packet_id', 'bidder_id', 'winning_amount'];
}
