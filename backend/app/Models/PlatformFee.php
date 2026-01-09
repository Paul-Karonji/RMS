<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformFee extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'fee_type',
        'fee_percentage',
        'fee_amount',
        'payment_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'base_amount' => 'decimal:2',
    ];

    /**
     * Get the payment that this fee belongs to
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the property that this fee is associated with
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the tenant that this fee belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
