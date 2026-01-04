<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBalance extends BaseTenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'available_balance',
        'pending_balance',
        'total_collected',
        'total_withdrawn',
        'last_cashout_at',
        'last_cashout_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'last_cashout_at' => 'datetime',
        'last_cashout_amount' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns this balance.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get formatted available balance.
     */
    public function formattedAvailableBalance(): string
    {
        return number_format($this->available_balance, 2);
    }

    /**
     * Get formatted pending balance.
     */
    public function formattedPendingBalance(): string
    {
        return number_format($this->pending_balance, 2);
    }

    /**
     * Check if balance is sufficient for cashout.
     */
    public function canCashout(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }
}
