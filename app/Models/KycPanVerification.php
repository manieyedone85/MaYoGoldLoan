<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycPanVerification extends Model
{
    protected $fillable = ['customer_id', 'pan_number', 'is_verified', 'name_match'];

    protected $casts = [
        'is_verified' => 'boolean',
        'name_match' => 'boolean',
    ];
}
