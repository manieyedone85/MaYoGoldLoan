<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = ['voucher_number', 'branch_id', 'type', 'voucher_date', 'source', 'created_by'];

    protected $casts = ['voucher_date' => 'date'];

    public function details(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
    }
}
