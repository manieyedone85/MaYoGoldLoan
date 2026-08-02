<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycAadhaarVerification extends Model
{
    protected $fillable = ['customer_id', 'method', 'uidai_reference_id', 'is_verified', 'verified_at'];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
}
