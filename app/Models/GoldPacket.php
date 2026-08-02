<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldPacket extends Model
{
    protected $fillable = ['packet_code', 'jewellery_item_id', 'vault_id', 'status'];

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }
}
