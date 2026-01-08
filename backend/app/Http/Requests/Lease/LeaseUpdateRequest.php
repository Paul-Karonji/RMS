<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class LeaseUpdateRequest extends FormRequest
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
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'rent_amount' => ['sometimes', 'numeric', 'min:0'],
            'payment_frequency' => ['sometimes', 'in:monthly,weekly'],
            'payment_day' => ['sometimes', 'integer', 'min:1', 'max:31'],
            'late_fee_type' => ['nullable', 'in:flat,percentage'],
            'late_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee_grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after' => 'End date must be after start date',
            'rent_amount.min' => 'Rent amount cannot be negative',
        ];
    }
}
