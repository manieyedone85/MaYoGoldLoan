<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    protected $fillable = ['customer_id', 'document_type_id', 'file_ref', 'status', 'verified_by'];
}
