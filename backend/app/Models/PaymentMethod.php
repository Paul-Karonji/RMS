<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'method_type',
        'provider',
        'account_number',
        'account_name',
        'expiry_date',
        'is_default',
        'is_active',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expiry_date' => 'date',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the user who owns this payment method.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get payments made with this payment method.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if payment method is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && (!$this->expiry_date || $this->expiry_date > now());
    }

    /**
     * Get method type label.
     */
    public function getMethodTypeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->method_type));
    }

    /**
     * Mask account number for display.
     */
    public function maskedAccountNumber(): string
    {
        if (strlen($this->account_number) <= 4) {
            return '****';
        }

        return '****' . substr($this->account_number, -4);
    }
}
