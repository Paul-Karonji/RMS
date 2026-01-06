<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class PropertyApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $propertyId = $this->route('id');
        
        if (!$propertyId) {
            return false;
        }

        $property = \App\Models\Property::find($propertyId);
        
        if (!$property) {
            return false;
        }

        $user = $this->user();
        
        if (!$user) {
            return false;
        }

        if (!$user->hasRole('company_admin')) {
            return false;
        }

        if ($property->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $property->status === 'pending_approval';
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();
        
        if ($action === 'reject') {
            return [
                'rejection_reason' => 'required|string|max:1000',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Please provide a reason for rejection',
            'rejection_reason.max' => 'Rejection reason cannot exceed 1000 characters',
        ];
    }
}
