<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaseSignature extends BaseUuidModel
{
    /** @use HasFactory<\Database\Factories\LeaseSignatureFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lease_id',
        'user_id',
        'signer_role',
        'signature_data',
        'signed_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'signature_data' => 'array',
        'signed_at' => 'datetime',
    ];

    /**
     * Get the lease for this signature.
     */
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get the user who signed.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a tenant signature.
     */
    public function isTenantSignature(): bool
    {
        return $this->signer_role === 'tenant';
    }

    /**
     * Check if this is an owner signature.
     */
    public function isOwnerSignature(): bool
    {
        return $this->signer_role === 'owner';
    }

    /**
     * Check if this is a witness signature.
     */
    public function isWitnessSignature(): bool
    {
        return $this->signer_role === 'witness';
    }
}
