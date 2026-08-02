<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldRelease extends Model
{
    protected $fillable = [
        'loan_id', 'jewellery_item_id', 'id_proof_verified', 'signature_captured',
        'photo_captured', 'released_by', 'released_to', 'status', 'released_at',
    ];

    protected $casts = [
        'id_proof_verified' => 'boolean',
        'signature_captured' => 'boolean',
        'photo_captured' => 'boolean',
        'released_at' => 'datetime',
    ];

    /**
     * All three checklist gates must be true before release can complete.
     */
    public function isReadyForRelease(): bool
    {
        return $this->id_proof_verified && $this->signature_captured && $this->photo_captured;
    }

    public function jewelleryItem(): BelongsTo
    {
        return $this->belongsTo(JewelleryItem::class);
    }
}
