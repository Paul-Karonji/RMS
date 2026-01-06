<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'unit_id' => $this->unit_id,
            'tenant_name' => $this->guest_name,
            'tenant_email' => $this->guest_email,
            'tenant_phone' => $this->guest_phone,
            'deposit_amount' => (float) $this->deposit_amount,
            'move_in_date' => $this->move_in_date,
            'reservation_date' => $this->reservation_date?->toISOString(),
            'expires_at' => $this->expiry_date?->toISOString(),
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'transaction_id' => $this->transaction_id,
            'notes' => $this->notes,

            // Computed fields
            'is_active' => $this->status === 'active' && $this->expiry_date > now(),
            'is_expired' => $this->status === 'active' && $this->expiry_date <= now(),
            'time_remaining' => $this->status === 'active' && $this->expiry_date > now()
                ? $this->expiry_date->diffForHumans(now(), true)
                : null,
            'days_remaining' => $this->status === 'active' && $this->expiry_date > now()
                ? $this->expiry_date->diffInDays(now())
                : 0,

            // Formatted values
            'formatted_deposit' => 'KES ' . number_format($this->deposit_amount, 0),

            // Unit info
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'unit_number' => $this->unit->unit_number,
                    'monthly_rent' => (float) $this->unit->monthly_rent,
                    'deposit_amount' => (float) $this->unit->deposit_amount,
                    'property' => $this->unit->relationLoaded('property') ? [
                        'id' => $this->unit->property->id,
                        'name' => $this->unit->property->property_name,
                    ] : null,
                ];
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
