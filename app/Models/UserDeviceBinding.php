<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDeviceBinding extends Model
{
    protected $fillable = ['user_id', 'device_id', 'device_model', 'push_token', 'is_active', 'bound_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'bound_at' => 'datetime',
    ];
}
