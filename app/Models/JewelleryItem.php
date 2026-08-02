<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JewelleryItem extends Model
{
    protected $fillable = [
        'barcode', 'customer_id', 'category_id', 'hallmark_flag',
        'gross_weight', 'stone_weight', 'net_weight', 'purity_karat',
        'gold_rate_id', 'applied_rate', 'eligible_percentage', 'eligible_amount',
        'evaluated_by', 'status', 'loan_id',
    ];

    protected $casts = [
        'hallmark_flag' => 'boolean',
        'gross_weight' => 'decimal:3',
        'stone_weight' => 'decimal:3',
        'net_weight' => 'decimal:3',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JewelleryCategory::class);
    }

    public function goldRate(): BelongsTo
    {
        return $this->belongsTo(GoldRate::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(JewelleryImage::class);
    }

    /**
     * Enforced net weight rule: gross - stone.
     * Also mirrored as a DB-level generated/trigger check in production.
     */
    public function recalculateNetWeight(): void
    {
        $this->net_weight = $this->gross_weight - $this->stone_weight;
    }
}
