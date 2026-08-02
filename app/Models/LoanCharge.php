<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanCharge extends Model
{
    protected $fillable = ['loan_id', 'charge_type', 'amount'];
}
