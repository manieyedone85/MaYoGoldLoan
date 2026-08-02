<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PacketTransferLog extends Model
{
    protected $fillable = ['gold_packet_id', 'from_vault_id', 'to_vault_id', 'transferred_by'];
}
