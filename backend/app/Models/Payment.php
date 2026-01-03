<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lease_id',
        'reservation_id',
        'subscription_invoice_id',
        'tenant_id',
        'payment_method_id',
        'amount',
        'payment_type',
        'payment_method',
        'payment_date',
        'status',
        'transaction_id',
        'reference_number',
        'description',
        'is_prorated',
        'prorated_days',
        'prorated_calculation',
        'failure_reason',
        'retry_count',
        'max_retries',
        'next_retry_at',
        'alternative_payment_suggested',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_date' => 'date',
        'next_retry_at' => 'datetime',
        'amount' => 'decimal:2',
        'is_prorated' => 'boolean',
        'alternative_payment_suggested' => 'boolean',
    ];

    /**
     * Get the lease for this payment.
     */
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get the reservation for this payment.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the subscription invoice for this payment.
     */
    public function subscriptionInvoice()
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    /**
     * Get the tenant (user) who made this payment.
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the payment method used for this payment.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get platform fees for this payment.
     */
    public function platformFees()
    {
        return $this->hasMany(PlatformFee::class);
    }

    /**
     * Get balance transactions for this payment.
     */
    public function balanceTransactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    /**
     * Get refunds for this payment.
     */
    public function refunds()
    {
        return $this->hasMany(Payment::class, 'refunded_payment_id');
    }

    /**
     * Get the original payment if this is a refund.
     */
    public function refundedPayment()
    {
        return $this->belongsTo(Payment::class, 'refunded_payment_id');
    }

    /**
     * Check if payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if payment can be retried.
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && 
               $this->retry_count < $this->max_retries &&
               $this->next_retry_at <= now();
    }

    /**
     * Get formatted amount.
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get payment type label.
     */
    public function getPaymentTypeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->payment_type));
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->payment_method));
    }
}
