<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class PropertyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $propertyId = $this->route('property');
        
        if (!$propertyId) {
            return false;
        }

        $property = \App\Models\Property::find($propertyId);
        
        if (!$property) {
            return false;
        }

        $user = $this->user();
        
        if (!$user) {
            return false;
        }

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

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'property_type' => 'sometimes|string|in:apartment,villa,townhouse,studio,penthouse,single_family,multi_family',
            'description' => 'nullable|string|max:2000',
            'address_line_1' => 'sometimes|string|max:500',
            'address_line_2' => 'nullable|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'sometimes|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_units' => 'sometimes|integer|min:1|max:1000',
            'commission_percentage' => 'sometimes|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Property name must be text',
            'property_type.in' => 'Invalid property type selected',
            'total_units.min' => 'Property must have at least 1 unit',
            'total_units.max' => 'Property cannot have more than 1000 units',
            'commission_percentage.min' => 'Commission percentage cannot be negative',
            'commission_percentage.max' => 'Commission percentage cannot exceed 100%',
        ];
    }
}
