<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Relationship to the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to only include models for the current tenant.
     */
    public function scopeForTenant(Builder $query, $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to only include models for the authenticated user's tenant.
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenantId = auth()->user()?->tenant_id;
        
        if (!$tenantId) {
            // For platform users or unauthenticated requests
            return $query;
        }
        
        return $query->where('tenant_id', $tenantId);
    }
}
