<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanReload extends Model
{
    protected $fillable = ['loan_id', 'excess_amount_eligible', 'reload_amount', 'processed_by'];
}
