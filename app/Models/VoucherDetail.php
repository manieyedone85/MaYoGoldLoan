<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherDetail extends Model
{
    protected $fillable = ['voucher_id', 'gl_account_id', 'debit', 'credit'];
}
