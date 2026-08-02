<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionSettlement extends Model
{
    protected $fillable = [
        'loan_id', 'gold_packet_id', 'outstanding_loan_amount',
        'auction_amount', 'remaining_balance_to_customer', 'settled_by',
    ];
}
