<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerBalance extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\OwnerBalanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',  // Added for multi-tenancy
        'property_owner_id',
        'total_rent_collected',  // Added for balance tracking
        'total_expenses',  // Added for balance tracking
        'amount_owed',
        'amount_paid',
        'last_payment_date',
        'last_payment_amount',
        'next_expected_payment_date',
        'total_earned',
        'total_paid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount_owed' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'last_payment_date' => 'date',
        'last_payment_amount' => 'decimal:2',
        'next_expected_payment_date' => 'date',
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    /**
     * Get the property owner for this balance.
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    /**
     * Get owner payments for this balance.
     */
    public function ownerPayments()
    {
        return $this->hasMany(OwnerPayment::class);
    }

    /**
     * Get formatted amount owed.
     */
    public function formattedAmountOwed(): string
    {
        return number_format($this->amount_owed, 2);
    }

    /**
     * Get formatted amount paid.
     */
    public function formattedAmountPaid(): string
    {
        return number_format($this->amount_paid, 2);
    }

    /**
     * Get total earnings.
     */
    public function formattedTotalEarned(): string
    {
        return number_format($this->total_earned, 2);
    }

    /**
     * Check if owner has outstanding balance.
     */
    public function hasOutstandingBalance(): bool
    {
        return $this->amount_owed > 0;
    }
}
