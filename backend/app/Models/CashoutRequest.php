<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashoutRequest extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\CashoutRequestFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'amount',
        'fee_amount',
        'net_amount',
        'status',
        'payment_method',
        'payment_details',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'transaction_id',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_details' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns this cashout request.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who approved this cashout request.
     */
    public function approvedBy()
    {
        return $this->belongsTo(PlatformUser::class, 'approved_by');
    }

    /**
     * Get the user who rejected this cashout request.
     */
    public function rejectedBy()
    {
        return $this->belongsTo(PlatformUser::class, 'rejected_by');
    }

    /**
     * Check if cashout request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if cashout request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if cashout request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if cashout request is processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Get formatted amount.
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get formatted net amount.
     */
    public function formattedNetAmount(): string
    {
        return number_format($this->net_amount, 2);
    }
}
