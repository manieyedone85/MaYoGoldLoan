<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    protected $fillable = [
        'code', 'name', 'interest_rate_pct', 'interest_type', 'tenure_months',
        'processing_fee_pct', 'gst_pct', 'insurance_pct', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
