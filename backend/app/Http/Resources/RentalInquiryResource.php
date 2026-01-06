<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalInquiryResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'preferred_move_in_date' => $this->preferred_move_in_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'follow_up_date' => $this->follow_up_date?->format('Y-m-d'),

            // Unit info (limited for public)
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'unit_number' => $this->unit->unit_number,
                    'monthly_rent' => (float) $this->unit->monthly_rent,
                    'location_area' => $this->unit->location_area,
                    'location_city' => $this->unit->location_city,
                ];
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
