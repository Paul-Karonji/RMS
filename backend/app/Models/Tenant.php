<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends BaseUuidModel
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_name',
        'pricing_model',
        'cashout_fee_percentage',
        'min_cashout_amount',
        'subscription_plan',
        'subscription_amount',
        'subscription_status',
        'subscription_started_at',
        'next_billing_date',
        'min_platform_fee_percentage',
        'max_platform_fee_percentage',
        'default_platform_fee_percentage',
        'admin_user_id',
        'admin_email',
        'admin_phone',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_swift_code',
        'mpesa_phone',
        'default_currency',
        'timezone',
        'default_rent_collection_day',
        'default_lease_terms',
        'logo_url',
        'status',
        'created_by',
    ];

    /**
     * Get the platform user who created this tenant.
     */
    public function creator()
    {
        return $this->belongsTo(PlatformUser::class, 'created_by');
    }

    /**
     * Get the admin user for this tenant.
     */
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Get all users belonging to this tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the company balance for this tenant.
     */
    public function companyBalance()
    {
        return $this->hasOne(CompanyBalance::class);
    }

    /**
     * Alias for companyBalance relationship.
     */
    public function balance()
    {
        return $this->companyBalance();
    }

    /**
     * Get subscription invoices for this tenant.
     */
    public function subscriptionInvoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * Get all properties for this tenant.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get all property owners for this tenant.
     */
    public function propertyOwners()
    {
        return $this->hasMany(PropertyOwner::class);
    }

    /**
     * Get all leases for this tenant.
     */
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    /**
     * Get all payments for this tenant.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all cashout requests for this tenant.
     */
    public function cashoutRequests()
    {
        return $this->hasMany(CashoutRequest::class);
    }

    /**
     * Get all notifications for this tenant.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all audit logs for this tenant.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get all balance transactions for this tenant.
     */
    public function balanceTransactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    /**
     * Scope a query to only include active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include payment processing tenants.
     */
    public function scopePaymentProcessing($query)
    {
        return $query->where('pricing_model', 'payment_processing');
    }

    /**
     * Scope a query to only include listings only tenants.
     */
    public function scopeListingsOnly($query)
    {
        return $query->where('pricing_model', 'listings_only');
    }
}
