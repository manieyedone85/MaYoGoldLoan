<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    protected $fillable = ['customer_id', 'loan_id', 'particulars', 'debit', 'credit'];
}
