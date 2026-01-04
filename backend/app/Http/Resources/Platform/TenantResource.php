<?php

namespace App\Http\Resources\Platform;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'company_email' => $this->company_email,
            'company_phone' => $this->company_phone,
            'company_address' => $this->company_address,
            
            // Pricing model
            'pricing_model' => $this->pricing_model,
            'cashout_fee_percentage' => $this->cashout_fee_percentage,
            'min_platform_fee_percentage' => $this->min_platform_fee_percentage,
            'max_platform_fee_percentage' => $this->max_platform_fee_percentage,
            'default_platform_fee_percentage' => $this->default_platform_fee_percentage,
            
            // Subscription
            'subscription_plan' => $this->subscription_plan,
            'subscription_amount' => $this->subscription_amount,
            'subscription_status' => $this->subscription_status,
            'subscription_started_at' => $this->subscription_started_at,
            'next_billing_date' => $this->next_billing_date,
            
            // Status
            'status' => $this->status,
            
            // Relationships
            'admin_user' => $this->whenLoaded('adminUser', function() {
                return [
                    'id' => $this->adminUser->id,
                    'name' => $this->adminUser->name,
                    'email' => $this->adminUser->email,
                    'phone' => $this->adminUser->phone,
                ];
            }),
            
            'balance' => $this->whenLoaded('balance', function() {
                return [
                    'available_balance' => $this->balance->available_balance,
                    'platform_fees_collected' => $this->balance->platform_fees_collected,
                    'deposits_held' => $this->balance->deposits_held,
                    'total_earned' => $this->balance->total_earned,
                    'total_cashed_out' => $this->balance->total_cashed_out,
                ];
            }),
            
            'properties_count' => $this->whenCounted('properties'),
            'users_count' => $this->whenCounted('users'),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
