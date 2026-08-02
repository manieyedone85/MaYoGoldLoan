<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDuplicateLog extends Model
{
    protected $fillable = ['customer_id', 'matched_customer_id', 'match_score', 'status', 'reviewed_by'];
}
