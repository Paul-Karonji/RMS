<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property): bool
    {
        if ($user->hasRole('property_owner')) {
            $propertyOwner = $user->propertyOwner;
            return $propertyOwner && $property->property_owner_id === $propertyOwner->id;
        }

        return $property->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('property_owner') && 
               $user->tenant !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->hasRole('property_owner')) {
            $propertyOwner = $user->propertyOwner;
            
            if (!$propertyOwner || $property->property_owner_id !== $propertyOwner->id) {
                return false;
            }

            return in_array($property->status, ['pending_approval', 'rejected']);
        }

        if ($user->hasRole(['company_admin', 'company_staff'])) {
            return $property->tenant_id === $user->tenant_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Property $property): bool
    {
        if (!$user->hasRole('company_admin')) {
            return false;
        }

        if ($property->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $property->activeLeases()->count() === 0;
    }

    /**
     * Determine whether the user can approve the property.
     */
    public function approve(User $user, Property $property): bool
    {
        return $user->hasRole('company_admin') &&
               $property->tenant_id === $user->tenant_id &&
               $property->status === 'pending_approval';
    }

    /**
     * Determine whether the user can assign a manager.
     */
    public function assignManager(User $user, Property $property): bool
    {
        return $user->hasRole('company_admin') &&
               $property->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Property $property): bool
    {
        return $user->hasRole('company_admin') &&
               $property->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Property $property): bool
    {
        return false;
    }
}
