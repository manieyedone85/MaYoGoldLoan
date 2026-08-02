<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApprovalLimit extends Model
{
    protected $fillable = ['role_id', 'max_amount'];
}
