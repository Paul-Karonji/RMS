<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionInvoice extends BaseUuidModel
{
    /** @use HasFactory<\Database\Factories\SubscriptionInvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'period_start',
        'period_end',
        'subscription_plan',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'transaction_id',
        'reminder_sent_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
