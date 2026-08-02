<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanClosure extends Model
{
    protected $fillable = ['loan_id', 'total_amount_collected', 'closure_date', 'closed_by'];

    protected $casts = ['closure_date' => 'date'];
}
