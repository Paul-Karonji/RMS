<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaseDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'rent_amount' => $this->rent_amount,
            'deposit_amount' => $this->deposit_amount,
            'payment_frequency' => $this->payment_frequency,
            'payment_day' => $this->payment_day,
            'payment_type' => $this->payment_type,
            'status' => $this->status,
            'late_fee_type' => $this->late_fee_type,
            'late_fee_amount' => $this->late_fee_amount,
            'late_fee_grace_period_days' => $this->late_fee_grace_period_days,
            'terms_source' => $this->terms_source,
            'terminated_at' => $this->terminated_at?->toISOString(),
            'termination_reason' => $this->termination_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
