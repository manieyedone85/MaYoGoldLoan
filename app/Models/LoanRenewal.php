<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRenewal extends Model
{
    protected $fillable = [
        'loan_id', 'renewed_tenure_months', 'interest_paid',
        'renewal_charges', 'new_due_date', 'processed_by',
    ];

    protected $casts = ['new_due_date' => 'date'];
}
