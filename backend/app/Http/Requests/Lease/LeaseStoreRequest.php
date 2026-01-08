<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class LeaseStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(['company_admin', 'company_staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'uuid', 'exists:properties,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'tenant_user_id' => ['required', 'uuid', 'exists:users,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'payment_frequency' => ['required', 'in:monthly,weekly'],
            'payment_day' => ['required', 'integer', 'min:1', 'max:31'],
            'payment_type' => ['required', 'in:recurring,manual'],
            'late_fee_type' => ['nullable', 'in:flat,percentage'],
            'late_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee_grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'terms_source' => ['nullable', 'in:property,custom'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'property_id.required' => 'Property is required',
            'property_id.exists' => 'Selected property does not exist',
            'unit_id.required' => 'Unit is required',
            'unit_id.exists' => 'Selected unit does not exist',
            'tenant_user_id.required' => 'Tenant is required',
            'tenant_user_id.exists' => 'Selected tenant does not exist',
            'start_date.required' => 'Lease start date is required',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
            'end_date.required' => 'Lease end date is required',
            'end_date.after' => 'End date must be after start date',
            'rent_amount.required' => 'Monthly rent amount is required',
            'deposit_amount.required' => 'Security deposit amount is required',
            'payment_type.in' => 'Payment type must be either recurring or manual',
        ];
    }
}
