<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PropertyDetailResource extends PropertyResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'approval_status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
            'approved_by' => $this->when($this->approvedBy, [
                'id' => $this->approvedBy->id ?? null,
                'name' => $this->approvedBy->name ?? null,
            ]),
            'units' => UnitResource::collection($this->whenLoaded('units')),
            'amenities' => PropertyAmenityResource::collection($this->whenLoaded('amenities')),
            'monthly_rental_income' => $this->monthlyRentalIncome(),
        ]);
    }
}
