<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncQueue extends Model
{
    protected $fillable = ['user_id', 'entity_type', 'payload', 'status'];

    protected $casts = ['payload' => 'array'];
}
