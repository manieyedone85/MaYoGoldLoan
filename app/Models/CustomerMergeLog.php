<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMergeLog extends Model
{
    protected $fillable = ['primary_customer_id', 'merged_customer_id', 'approved_by'];
}
