<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUnitResource extends JsonResource
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
            'monthly_rent' => (float) $this->monthly_rent,
            'deposit_amount' => (float) $this->deposit_amount,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'size_sqft' => $this->size_sqft,
            'floor_level' => $this->floor_level,
            'description' => $this->description,
            'is_furnished' => $this->is_furnished,
            'allow_pets' => $this->allow_pets,
            'parking_available' => $this->parking_available,
            'amenities' => $this->amenities ?? [],
            'available_from' => $this->available_from,
            'is_available' => $this->status === 'available' && !$this->activeReservation,
            
            // Property info (limited)
            'property' => $this->whenLoaded('property', function () {
                return [
                    'id' => $this->property->id,
                    'name' => $this->property->property_name,
                    'property_type' => $this->property->property_type,
                    'city' => $this->property->city,
                    'county' => $this->property->county,
                    'address' => $this->property->address ?? null,
                    'description' => $this->property->description ?? null,
                ];
            }),

            // Photos
            'photos' => $this->whenLoaded('photos', function () {
                return $this->photos->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'url' => $photo->photo_url,
                        'is_primary' => $photo->is_primary,
                        'sort_order' => $photo->sort_order,
                    ];
                });
            }),

            // Primary photo for listings
            'primary_photo' => $this->whenLoaded('photos', function () {
                $primary = $this->photos->firstWhere('is_primary', true);
                return $primary ? $primary->photo_url : ($this->photos->first()?->photo_url ?? null);
            }),

            // Formatted values
            'formatted_rent' => 'KES ' . number_format($this->monthly_rent, 0),
            'formatted_deposit' => 'KES ' . number_format($this->deposit_amount, 0),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
