<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lease_id' => ['required', 'uuid', 'exists:leases,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_type' => ['required', 'in:rent,deposit,reservation'],
            'payment_method' => ['required', 'in:stripe,mpesa'],
            'mpesa_phone' => ['required_if:payment_method,mpesa', 'string', 'regex:/^(\+?254|0)[17]\d{8}$/'],
            'due_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'lease_id.required' => 'Lease ID is required',
            'lease_id.exists' => 'Invalid lease ID',
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Payment amount must be at least 1',
            'payment_type.required' => 'Payment type is required',
            'payment_type.in' => 'Payment type must be rent, deposit, or reservation',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Payment method must be stripe or mpesa',
            'mpesa_phone.required_if' => 'M-Pesa phone number is required for M-Pesa payments',
            'mpesa_phone.regex' => 'Invalid M-Pesa phone number format. Use 254XXXXXXXXX or 07XXXXXXXX',
        ];
    }
}
