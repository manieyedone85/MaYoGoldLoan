<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionNoticeLog extends Model
{
    protected $fillable = ['auction_schedule_id', 'loan_id', 'channel', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];
}
