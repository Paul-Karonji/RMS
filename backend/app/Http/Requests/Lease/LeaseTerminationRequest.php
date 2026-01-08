<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class LeaseTerminationRequest extends FormRequest
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
            'termination_reason' => ['required', 'string', 'max:500'],
            'termination_date' => ['required', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'termination_reason.required' => 'Please provide a reason for lease termination',
            'termination_date.required' => 'Termination date is required',
        ];
    }
}
