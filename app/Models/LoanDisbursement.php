<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanDisbursement extends Model
{
    protected $fillable = ['loan_id', 'mode', 'amount', 'reference_number', 'status', 'disbursed_by'];
}
