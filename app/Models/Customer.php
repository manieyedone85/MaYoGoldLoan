<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code', 'name', 'mobile', 'email', 'dob', 'gender',
        'aadhaar_last4', 'aadhaar_hash', 'pan_number', 'branch_id',
        'registered_by', 'kyc_status', 'is_blacklisted',
    ];

    protected $hidden = ['aadhaar_hash'];

    protected $casts = [
        'dob' => 'date',
        'is_blacklisted' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(CustomerFamilyMember::class);
    }

    public function nominees(): HasMany
    {
        return $this->hasMany(CustomerNominee::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function jewelleryItems(): HasMany
    {
        return $this->hasMany(JewelleryItem::class);
    }
}
