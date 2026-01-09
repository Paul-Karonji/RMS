<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BalanceTransaction extends BaseUuidModel
{
    /** @use HasFactory<\Database\Factories\BalanceTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'property_owner_id',
        'transaction_type',
        'amount',
        'fee_amount',
        'net_amount',
        'transaction_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the payment that this transaction belongs to
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the property owner for this transaction
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    /**
     * Get the tenant that this transaction belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
