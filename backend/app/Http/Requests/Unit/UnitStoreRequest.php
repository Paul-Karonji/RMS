<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitStoreRequest extends FormRequest
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

        if ($property->status !== 'active') {
            return false;
        }

        if ($user->hasRole('company_admin')) {
            return $property->tenant_id === $user->tenant_id;
        }

        if ($user->hasRole('company_staff')) {
            return $property->tenant_id === $user->tenant_id && 
                   $property->manager_id === $user->id;
        }

        return false;
    }

    public function rules(): array
    {
        $propertyId = $this->route('property');

        return [
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')
                    ->where('property_id', $propertyId)
            ],
            'unit_type' => 'required|string|max:100',
            'bedrooms' => 'required|integer|min:0|max:20',
            'bathrooms' => 'required|integer|min:0|max:20',
            'size_sqft' => 'nullable|numeric|min:1',
            'floor_level' => 'nullable|string|max:50',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'is_furnished' => 'boolean',
            'allow_pets' => 'boolean',
            'parking_available' => 'boolean',
            'parking_spaces' => 'nullable|integer|min:0|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'unit_number.required' => 'Unit number is required',
            'unit_number.unique' => 'This unit number already exists for this property',
            'unit_type.required' => 'Unit type is required',
            'bedrooms.required' => 'Number of bedrooms is required',
            'bedrooms.min' => 'Bedrooms cannot be negative',
            'bedrooms.max' => 'Maximum 20 bedrooms allowed',
            'bathrooms.required' => 'Number of bathrooms is required',
            'bathrooms.min' => 'Bathrooms cannot be negative',
            'bathrooms.max' => 'Maximum 20 bathrooms allowed',
            'monthly_rent.required' => 'Monthly rent is required',
            'monthly_rent.min' => 'Rent amount cannot be negative',
            'deposit_amount.required' => 'Deposit amount is required',
            'deposit_amount.min' => 'Deposit amount cannot be negative',
            'floor_level.min' => 'Floor level cannot be below -5 (basement)',
            'floor_level.max' => 'Floor level cannot exceed 200',
        ];
    }
}
