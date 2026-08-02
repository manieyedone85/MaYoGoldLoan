<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionSchedule extends Model
{
    protected $fillable = ['branch_id', 'auction_date', 'status', 'created_by'];

    protected $casts = ['auction_date' => 'date'];

    public function noticeLogs(): HasMany
    {
        return $this->hasMany(AuctionNoticeLog::class);
    }

    public function bidders(): HasMany
    {
        return $this->hasMany(AuctionBidder::class);
    }
}
