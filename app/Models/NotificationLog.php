<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['customer_id', 'template_id', 'channel', 'status', 'retry_count'];
}
