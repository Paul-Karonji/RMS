<?php

namespace App\Http\Resources\Platform;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyBalanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'available_balance' => $this->available_balance,
            'platform_fees_collected' => $this->platform_fees_collected,
            'deposits_held' => $this->deposits_held,
            'reservations_collected' => $this->reservations_collected,
            'total_earned' => $this->total_earned,
            'total_cashed_out' => $this->total_cashed_out,
            'total_platform_fees_paid' => $this->total_platform_fees_paid,
            'updated_at' => $this->updated_at,
            
            'tenant' => $this->whenLoaded('tenant', function() {
                return [
                    'id' => $this->tenant->id,
                    'company_name' => $this->tenant->company_name,
                ];
            }),
        ];
    }
}
