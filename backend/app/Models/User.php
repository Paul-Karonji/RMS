<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens;
    
    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    
    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password_hash',
        'role',
        'account_type',
        'must_change_password',
        'credentials_sent_at',
        'first_login_at',
        'last_login_at',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'credentials_sent_at' => 'datetime',
            'first_login_at' => 'datetime',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Get the tenant (company) this user belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created this user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get users created by this user.
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * If this user is a tenant admin, get the tenant they administer.
     */
    public function administeredTenant()
    {
        return $this->hasOne(Tenant::class, 'admin_user_id');
    }

    /**
     * Get property owner record for this user.
     */
    public function propertyOwner()
    {
        return $this->hasOne(PropertyOwner::class);
    }

    /**
     * Get leases where this user is the tenant.
     */
    public function leases()
    {
        return $this->hasMany(Lease::class, 'tenant_id');
    }

    /**
     * Get payments made by this user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    /**
     * Get payment methods for this user.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get maintenance requests reported by this user.
     */
    public function reportedMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'reported_by');
    }

    /**
     * Get maintenance requests assigned to this user.
     */
    public function assignedMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }

    /**
     * Get maintenance updates created by this user.
     */
    public function maintenanceUpdates()
    {
        return $this->hasMany(MaintenanceUpdate::class, 'updated_by');
    }

    /**
     * Get notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get audit logs for this user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get properties approved by this user.
     */
    public function approvedProperties()
    {
        return $this->hasMany(Property::class, 'approved_by');
    }

    /**
     * Get properties managed by this user.
     */
    public function managedProperties()
    {
        return $this->hasMany(Property::class, 'manager_id');
    }

    /**
     * Get expenses approved by this user.
     */
    public function approvedExpenses()
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    /**
     * Get expenses rejected by this user.
     */
    public function rejectedExpenses()
    {
        return $this->hasMany(Expense::class, 'rejected_by');
    }

    /**
     * Get expenses created by this user.
     */
    public function createdExpenses()
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    /**
     * Get lease signatures for this user.
     */
    public function leaseSignatures()
    {
        return $this->hasMany(LeaseSignature::class);
    }

    /**
     * Get owner payments created by this user.
     */
    public function ownerPayments()
    {
        return $this->hasMany(OwnerPayment::class, 'created_by');
    }

    /**
     * Check if user is a platform owner.
     */
    public function isPlatformOwner(): bool
    {
        return $this->role === 'platform_owner';
    }

    /**
     * Check if user is a company admin.
     */
    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    /**
     * Check if user is a property manager.
     */
    public function isPropertyManager(): bool
    {
        return $this->role === 'property_manager';
    }

    /**
     * Check if user is a property owner.
     */
    public function isPropertyOwner(): bool
    {
        return $this->role === 'property_owner';
    }

    /**
     * Check if user is a rental tenant.
     */
    public function isRentalTenant(): bool
    {
        return $this->role === 'tenant';
    }

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
