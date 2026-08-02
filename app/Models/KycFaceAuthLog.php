<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycFaceAuthLog extends Model
{
    protected $fillable = ['customer_id', 'is_matched', 'confidence_score'];

    protected $casts = ['is_matched' => 'boolean'];
}
