<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = ['customer_id', 'type', 'line1', 'line2', 'city', 'state', 'pincode'];
}
