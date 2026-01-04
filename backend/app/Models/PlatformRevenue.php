<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlatformRevenue extends BaseUuidModel
{
    protected $table = 'platform_revenue';

    protected $fillable = [
        'tenant_id',
        'revenue_source',
        'cashout_request_id',
        'subscription_invoice_id',
        'period_start',
        'period_end',
        'company_gross_revenue',
        'platform_revenue_percentage',
        'platform_revenue_amount',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'company_gross_revenue' => 'decimal:2',
        'platform_revenue_percentage' => 'decimal:2',
        'platform_revenue_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function cashoutRequest()
    {
        return $this->belongsTo(CashoutRequest::class, 'cashout_request_id');
    }

    public function subscriptionInvoice()
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'subscription_invoice_id');
    }
}
