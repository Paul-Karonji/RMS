<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashoutRequestRequest extends FormRequest
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
            'amount' => 'required|numeric|min:1000|max:10000000',
            'payment_method' => 'required|in:bank_transfer,mpesa',
            'payment_details' => 'required|array',
            'payment_details.account_number' => 'required_if:payment_method,bank_transfer|string|max:50',
            'payment_details.bank_name' => 'required_if:payment_method,bank_transfer|string|max:100',
            'payment_details.account_name' => 'required_if:payment_method,bank_transfer|string|max:100',
            'payment_details.phone_number' => 'required_if:payment_method,mpesa|string|regex:/^254[0-9]{9}$/',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum cashout amount is KES 1,000',
            'amount.max' => 'Maximum cashout amount is KES 10,000,000',
            'payment_details.phone_number.regex' => 'Phone number must be in format 254XXXXXXXXX',
        ];
    }
}
