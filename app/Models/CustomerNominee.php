<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNominee extends Model
{
    protected $fillable = ['customer_id', 'name', 'relation', 'mobile', 'id_proof_type', 'id_proof_number'];
}
