<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class PropertyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && 
               $this->user()->hasRole('property_owner') &&
               $this->user()->tenant !== null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,villa,townhouse,studio,penthouse,single_family,multi_family',
            'description' => 'nullable|string|max:2000',
            'address_line_1' => 'required|string|max:500',
            'address_line_2' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_units' => 'required|integer|min:1|max:1000',
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Property name is required',
            'property_type.required' => 'Property type is required',
            'property_type.in' => 'Invalid property type selected',
            'address_line_1.required' => 'Property address is required',
            'city.required' => 'City is required',
            'state.required' => 'State/County is required',
            'country.required' => 'Country is required',
            'total_units.required' => 'Total number of units is required',
            'total_units.min' => 'Property must have at least 1 unit',
            'total_units.max' => 'Property cannot have more than 1000 units',
            'commission_percentage.required' => 'Commission percentage is required',
            'commission_percentage.min' => 'Commission percentage cannot be negative',
            'commission_percentage.max' => 'Commission percentage cannot exceed 100%',
        ];
    }
}
