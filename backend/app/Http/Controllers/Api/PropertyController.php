<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyStoreRequest;
use App\Http\Requests\Property\PropertyUpdateRequest;
use App\Http\Resources\PropertyDetailResource;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\OwnerBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Property::with(['propertyOwner', 'manager'])
            ->withCount('units');

        if ($user->hasRole('property_owner')) {
            $propertyOwner = $user->propertyOwner;
            if (!$propertyOwner) {
                return ApiResponse::error('Property owner profile not found', 404);
            }
            $query->where('property_owner_id', $propertyOwner->id);
        } else {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        $properties = $query->paginate($request->get('per_page', 20));

        return ApiResponse::success(
            PropertyResource::collection($properties)->response()->getData(true)
        );
    }

    public function store(PropertyStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $propertyOwner = $user->propertyOwner;

            if (!$propertyOwner) {
                return ApiResponse::error('Property owner profile not found', 404);
            }

            $property = Property::create([
                'tenant_id' => $user->tenant_id,
                'property_owner_id' => $propertyOwner->id,
                'property_name' => $request->name,
                'property_type' => $request->property_type,
                'description' => $request->description,
                'address' => $request->address_line_1 . ($request->address_line_2 ? ', ' . $request->address_line_2 : ''),
                'city' => $request->city,
                'county' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'total_units' => $request->total_units,
                'occupied_units' => 0,
                'vacant_units' => $request->total_units,
                'status' => 'pending_approval',
                'commission_percentage' => $request->commission_percentage,
            ]);

            OwnerBalance::firstOrCreate(
                [
                    'tenant_id' => $user->tenant_id,
                    'property_owner_id' => $propertyOwner->id,
                ],
                [
                    'amount_owed' => 0,
                    'total_rent_collected' => 0,
                    'total_platform_fees' => 0,
                    'total_expenses' => 0,
                    'total_earned' => 0,
                    'total_paid' => 0,
                ]
            );

            DB::commit();

            return ApiResponse::success(
                new PropertyResource($property),
                'Property registered successfully. Awaiting approval.',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to register property: ' . $e->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $property = Property::with([
            'propertyOwner',
            'manager',
            'approvedBy',
            'units.photos',
            'amenities'
        ])->find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        if ($user->hasRole('property_owner')) {
            $propertyOwner = $user->propertyOwner;
            if (!$propertyOwner || $property->property_owner_id !== $propertyOwner->id) {
                return ApiResponse::error('Unauthorized', 403);
            }
        } else {
            if ($property->tenant_id !== $user->tenant_id) {
                return ApiResponse::error('Unauthorized', 403);
            }
        }

        return ApiResponse::success(new PropertyDetailResource($property));
    }

    public function update(PropertyUpdateRequest $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        try {
            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['property_name'] = $request->name;
            }
            if ($request->has('property_type')) {
                $updateData['property_type'] = $request->property_type;
            }
            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }
            if ($request->has('address_line_1') || $request->has('address_line_2')) {
                $address = $request->address_line_1 ?? '';
                if ($request->address_line_2) {
                    $address .= ($address ? ', ' : '') . $request->address_line_2;
                }
                $updateData['address'] = $address;
            }
            if ($request->has('city')) {
                $updateData['city'] = $request->city;
            }
            if ($request->has('state')) {
                $updateData['county'] = $request->state;
            }
            if ($request->has('postal_code')) {
                $updateData['postal_code'] = $request->postal_code;
            }
            if ($request->has('country')) {
                $updateData['country'] = $request->country;
            }
            if ($request->has('latitude')) {
                $updateData['latitude'] = $request->latitude;
            }
            if ($request->has('longitude')) {
                $updateData['longitude'] = $request->longitude;
            }
            if ($request->has('total_units')) {
                $updateData['total_units'] = $request->total_units;
                $updateData['vacant_units'] = $request->total_units - $property->occupied_units;
            }
            if ($request->has('commission_percentage')) {
                $updateData['commission_percentage'] = $request->commission_percentage;
            }
            
            $property->update($updateData);

            return ApiResponse::success(
                new PropertyResource($property),
                'Property updated successfully'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update property: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->hasRole('company_admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $property = Property::find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        if ($property->tenant_id !== $user->tenant_id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $activeLeases = $property->activeLeases()->count();
        if ($activeLeases > 0) {
            return ApiResponse::error('Cannot delete property with active leases', 400);
        }

        try {
            DB::beginTransaction();

            // Soft delete by updating status
            $property->update(['status' => 'deleted']);

            DB::commit();

            return ApiResponse::success(null, 'Property deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete property: ' . $e->getMessage(), 500);
        }
    }

    public function resubmit(Request $request, $id)
    {
        $user = $request->user();
        $property = Property::find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        $propertyOwner = $user->propertyOwner;
        if (!$propertyOwner || $property->property_owner_id !== $propertyOwner->id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        if ($property->status !== 'rejected') {
            return ApiResponse::error('Only rejected properties can be resubmitted', 400);
        }

        try {
            $property->update([
                'status' => 'pending_approval',
                'rejection_reason' => null,
            ]);

            return ApiResponse::success(
                new PropertyResource($property),
                'Property resubmitted for approval'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to resubmit property: ' . $e->getMessage(), 500);
        }
    }

    public function assignManager(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->hasRole('company_admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $property = Property::find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        if ($property->tenant_id !== $user->tenant_id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $request->validate([
            'manager_id' => 'required|exists:users,id',
        ]);

        $manager = \App\Models\User::find($request->manager_id);

        if (!$manager || $manager->tenant_id !== $user->tenant_id) {
            return ApiResponse::error('Invalid manager', 400);
        }

        if (!$manager->hasRole('company_staff')) {
            return ApiResponse::error('Manager must be a company staff member', 400);
        }

        try {
            $property->update(['manager_id' => $request->manager_id]);

            return ApiResponse::success(
                new PropertyResource($property->load('manager')),
                'Property manager assigned successfully'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to assign manager: ' . $e->getMessage(), 500);
        }
    }
}
