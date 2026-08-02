<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterestCollection extends Model
{
    protected $fillable = ['loan_id', 'amount', 'mode', 'receipt_number', 'collected_by'];
}
