<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'name' => $this->property_name,
            'property_type' => $this->property_type,
            'address' => $this->address,
            'city' => $this->city,
            'county' => $this->county,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'description' => $this->description,
            'total_units' => $this->total_units,
            'occupied_units' => $this->occupied_units,
            'vacant_units' => $this->vacant_units,
            'status' => $this->status,
            'commission_percentage' => $this->commission_percentage,
            'occupancy_rate' => $this->occupancyRate(),
            'owner' => [
                'id' => $this->propertyOwner->id ?? null,
                'name' => $this->propertyOwner->owner_name ?? null,
                'email' => $this->propertyOwner->email ?? null,
            ],
            'manager' => $this->when($this->manager, [
                'id' => $this->manager->id ?? null,
                'name' => $this->manager->name ?? null,
            ]),
            'units_count' => $this->whenCounted('units'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
