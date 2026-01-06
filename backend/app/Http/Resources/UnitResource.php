<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
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
            'unit_number' => $this->unit_number,
            'unit_type' => $this->unit_type,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'size_sqft' => $this->size_sqft,
            'floor_level' => $this->floor_level,
            'monthly_rent' => $this->monthly_rent,
            'deposit_amount' => $this->deposit_amount,
            'status' => $this->status,
            'description' => $this->description,
            'commission_percentage' => $this->commission_percentage,
            'is_furnished' => $this->is_furnished,
            'allow_pets' => $this->allow_pets,
            'parking_available' => $this->parking_available,
            'parking_spaces' => $this->parking_spaces,
            'property' => [
                'id' => $this->property->id ?? null,
                'name' => $this->property->property_name ?? null,
                'address' => $this->property->address ?? null,
            ],
            'photos' => UnitPhotoResource::collection($this->whenLoaded('photos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
