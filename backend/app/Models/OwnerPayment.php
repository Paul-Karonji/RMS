<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerPayment extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\OwnerPaymentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'property_owner_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the property owner for this payment.
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    /**
     * Get the user who created this payment record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted amount.
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount, 2);
    }
}
