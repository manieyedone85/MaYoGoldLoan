<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApprovalWorkflow extends Model
{
    protected $fillable = ['loan_id', 'current_stage', 'status'];
}
