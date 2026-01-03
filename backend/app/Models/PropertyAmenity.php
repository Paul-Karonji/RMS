<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyAmenity extends BaseUuidModel
{
    /** @use HasFactory<\Database\Factories\PropertyAmenityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'amenity_type',
        'name',
        'description',
        'quantity',
        'is_available',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Get the property for this amenity.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get amenity type label.
     */
    public function getAmenityTypeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->amenity_type));
    }
}
