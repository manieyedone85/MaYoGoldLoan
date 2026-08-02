<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPartPayment extends Model
{
    protected $fillable = ['loan_id', 'principal_amount', 'interest_amount', 'collected_by'];
}
