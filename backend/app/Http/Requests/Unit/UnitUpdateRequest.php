<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $unitId = $this->route('unit');
        
        if (!$unitId) {
            return false;
        }

        $unit = \App\Models\Unit::with('property')->find($unitId);
        
        if (!$unit) {
            return false;
        }

        $user = $this->user();
        
        if (!$user) {
            return false;
        }

        $property = $unit->property;

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
        $unitId = $this->route('unit');
        $unit = \App\Models\Unit::find($unitId);
        $propertyId = $unit->property_id ?? null;
        $isOccupied = $unit && $unit->status === 'occupied';

        $rules = [
            'unit_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')
                    ->where('property_id', $propertyId)
                    ->ignore($unit->id ?? null)
            ],
            'unit_type' => 'sometimes|string|max:100',
            'bedrooms' => 'sometimes|integer|min:0|max:20',
            'bathrooms' => 'sometimes|integer|min:0|max:20',
            'size_sqft' => 'nullable|numeric|min:1',
            'floor_level' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'is_furnished' => 'boolean',
            'allow_pets' => 'boolean',
            'parking_available' => 'boolean',
            'parking_spaces' => 'nullable|integer|min:0|max:10',
        ];

        if (!$isOccupied) {
            $rules['monthly_rent'] = 'sometimes|numeric|min:0';
            $rules['deposit_amount'] = 'sometimes|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'unit_number.unique' => 'This unit number already exists for this property',
            'bedrooms.min' => 'Bedrooms cannot be negative',
            'bedrooms.max' => 'Maximum 20 bedrooms allowed',
            'bathrooms.min' => 'Bathrooms cannot be negative',
            'bathrooms.max' => 'Maximum 20 bathrooms allowed',
            'monthly_rent.min' => 'Rent amount cannot be negative',
            'deposit_amount.min' => 'Deposit amount cannot be negative',
            'floor_level.min' => 'Floor level cannot be below -5 (basement)',
            'floor_level.max' => 'Floor level cannot exceed 200',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $unitId = $this->route('unit');
            $unit = \App\Models\Unit::find($unitId);
            
            if ($unit && $unit->status === 'occupied') {
                if ($this->has('monthly_rent') || $this->has('deposit_amount')) {
                    $validator->errors()->add(
                        'unit_status',
                        'Cannot change rent or deposit for occupied units'
                    );
                }
            }
        });
    }
}
