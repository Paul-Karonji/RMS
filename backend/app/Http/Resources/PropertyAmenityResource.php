<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyAmenityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amenity_type' => $this->amenity_type,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'is_available' => $this->is_available,
            'created_at' => $this->created_at,
        ];
    }
}
