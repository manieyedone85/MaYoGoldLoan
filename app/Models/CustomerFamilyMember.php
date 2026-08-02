<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFamilyMember extends Model
{
    protected $fillable = ['customer_id', 'name', 'relation', 'mobile'];
}
