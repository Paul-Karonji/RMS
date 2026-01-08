<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class LeaseRenewalRequest extends FormRequest
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
            'new_end_date' => ['required', 'date', 'after:current_end_date'],
            'new_rent_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'new_end_date.required' => 'New end date is required for lease renewal',
            'new_end_date.after' => 'New end date must be after current end date',
            'new_rent_amount.min' => 'Rent amount cannot be negative',
        ];
    }
}
