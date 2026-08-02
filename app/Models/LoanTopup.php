<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanTopup extends Model
{
    protected $fillable = [
        'loan_id', 'eligible_topup_amount', 'approved_amount',
        'processing_fee', 'status', 'approved_by',
    ];
}
