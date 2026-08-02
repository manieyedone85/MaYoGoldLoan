<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    protected $fillable = [
        'loan_account_number', 'customer_id', 'branch_id', 'loan_product_id',
        'eligible_amount', 'sanctioned_amount', 'interest_rate_pct',
        'processing_fee', 'gst_amount', 'insurance_amount', 'net_disbursed_amount',
        'loan_date', 'due_date', 'status', 'created_by',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(LoanCharge::class);
    }

    public function approvalWorkflow(): HasOne
    {
        return $this->hasOne(LoanApprovalWorkflow::class);
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(LoanApprovalLog::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(LoanDisbursement::class);
    }

    public function jewelleryItems(): HasMany
    {
        return $this->hasMany(JewelleryItem::class);
    }

    public function topups(): HasMany
    {
        return $this->hasMany(LoanTopup::class);
    }

    public function interestCollections(): HasMany
    {
        return $this->hasMany(InterestCollection::class);
    }
}
