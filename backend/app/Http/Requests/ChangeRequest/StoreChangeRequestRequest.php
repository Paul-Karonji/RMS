<?php

namespace App\Http\Requests\ChangeRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreChangeRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('property_owner');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id' => 'nullable|uuid|exists:properties,id',
            'unit_id' => 'required_if:request_type,unit_price,unit_condition|nullable|uuid|exists:units,id',
            'request_type' => 'required|in:unit_price,unit_condition,fee_structure,manager_change,property_details',
            'current_value' => 'required|string',
            'requested_value' => 'required|string',
            'reason' => 'required|string|min:10|max:1000',
            'affects_existing_leases' => 'required|boolean',
            'effective_from' => 'nullable|date|after:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'request_type.required' => 'Please specify the type of change you are requesting.',
            'request_type.in' => 'Invalid request type selected.',
            'reason.required' => 'Please provide a reason for this change request.',
            'reason.min' => 'The reason must be at least 10 characters.',
            'unit_id.required_if' => 'Unit is required for unit-specific change requests.',
        ];
    }
}
