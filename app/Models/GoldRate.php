<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldRate extends Model
{
    protected $fillable = [
        'rate_per_gram', 'karat', 'effective_date', 'status',
        'proposed_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'approved_at' => 'datetime',
        'rate_per_gram' => 'decimal:2',
    ];
}
