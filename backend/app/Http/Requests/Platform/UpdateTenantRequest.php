<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check() && auth('platform')->user()->role === 'platform_owner';
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant')->id;

        return [
            'company_name' => 'sometimes|string|max:255',
            'company_email' => [
                'sometimes',
                'email',
                Rule::unique('tenants', 'company_email')->ignore($tenantId),
            ],
            'company_phone' => 'sometimes|string|max:20',
            'company_address' => 'nullable|string|max:1000',
            'cashout_fee_percentage' => 'sometimes|numeric|min:0|max:10',
            'min_platform_fee_percentage' => 'sometimes|numeric|min:0|max:20',
            'max_platform_fee_percentage' => 'sometimes|numeric|min:0|max:30',
            'default_platform_fee_percentage' => 'sometimes|numeric|min:0|max:25',
            'subscription_plan' => ['sometimes', Rule::in(['weekly', 'monthly', 'annual'])],
        ];
    }
}
