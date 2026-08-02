<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['entity_type', 'entity_id', 'action', 'before_value', 'after_value', 'actor_id'];

    protected $casts = [
        'before_value' => 'array',
        'after_value' => 'array',
    ];
}
