<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check() && auth('platform')->user()->role === 'platform_owner';
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_phone' => 'required|string|max:20',
            'admin_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            
            // Pricing model
            'pricing_model' => ['required', Rule::in(['payment_processing', 'listings_only'])],
            
            // Payment processing fields
            'cashout_fee_percentage' => 'required_if:pricing_model,payment_processing|nullable|numeric|min:0|max:10',
            'min_platform_fee_percentage' => 'required_if:pricing_model,payment_processing|nullable|numeric|min:0|max:20',
            'max_platform_fee_percentage' => 'required_if:pricing_model,payment_processing|nullable|numeric|min:0|max:30|gte:min_platform_fee_percentage',
            'default_platform_fee_percentage' => 'required_if:pricing_model,payment_processing|nullable|numeric|min:0|max:25|gte:min_platform_fee_percentage|lte:max_platform_fee_percentage',
            
            // Listings only fields
            'subscription_plan' => ['required_if:pricing_model,listings_only', 'nullable', Rule::in(['weekly', 'monthly', 'annual'])],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_email.unique' => 'This email is already registered in the system.',
            'max_platform_fee_percentage.gte' => 'Maximum fee must be greater than or equal to minimum fee.',
            'default_platform_fee_percentage.gte' => 'Default fee must be within the min-max range.',
            'default_platform_fee_percentage.lte' => 'Default fee must be within the min-max range.',
        ];
    }
}
