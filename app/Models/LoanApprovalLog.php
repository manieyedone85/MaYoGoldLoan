<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApprovalLog extends Model
{
    protected $fillable = ['loan_id', 'stage', 'action', 'actioned_by', 'remarks'];
}
