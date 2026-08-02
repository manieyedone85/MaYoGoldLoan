<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionBidder extends Model
{
    protected $fillable = ['auction_schedule_id', 'name', 'mobile', 'id_proof_number'];
}
