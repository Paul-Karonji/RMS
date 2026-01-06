<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\UnitStoreRequest;
use App\Http\Requests\Unit\UnitUpdateRequest;
use App\Http\Resources\UnitResource;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Unit::with(['property', 'photos']);

        $query->whereHas('property', function ($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id);
        });

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->has('min_rent')) {
            $query->where('monthly_rent', '>=', $request->min_rent);
        }

        if ($request->has('max_rent')) {
            $query->where('monthly_rent', '<=', $request->max_rent);
        }

        $units = $query->paginate($request->get('per_page', 20));

        return ApiResponse::success(
            UnitResource::collection($units)->response()->getData(true)
        );
    }

    public function store(UnitStoreRequest $request, $propertyId)
    {
        $property = Property::find($propertyId);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        try {
            DB::beginTransaction();

            $unit = Unit::create([
                'property_id' => $property->id,
                'unit_number' => $request->unit_number,
                'unit_type' => $request->unit_type,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'size_sqft' => $request->size_sqft,
                'floor_level' => $request->floor_level,
                'monthly_rent' => $request->monthly_rent,
                'deposit_amount' => $request->deposit_amount,
                'status' => 'available',
                'description' => $request->description,
                'commission_percentage' => $request->commission_percentage,
                'is_furnished' => $request->boolean('is_furnished', false),
                'allow_pets' => $request->boolean('allow_pets', false),
                'parking_available' => $request->boolean('parking_available', false),
                'parking_spaces' => $request->parking_spaces ?? 0,
            ]);

            DB::commit();

            return ApiResponse::success(
                new UnitResource($unit->load('property')),
                'Unit added successfully',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to add unit: ' . $e->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $unit = Unit::with(['property', 'photos'])->find($id);

        if (!$unit) {
            return ApiResponse::error('Unit not found', 404);
        }

        if ($unit->property->tenant_id !== $user->tenant_id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        return ApiResponse::success(new UnitResource($unit));
    }

    public function update(UnitUpdateRequest $request, $id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return ApiResponse::error('Unit not found', 404);
        }

        try {
            $updateData = $request->only([
                'unit_number',
                'unit_type',
                'bedrooms',
                'bathrooms',
                'size_sqft',
                'floor_level',
                'description',
                'commission_percentage',
                'is_furnished',
                'allow_pets',
                'parking_available',
                'parking_spaces',
            ]);

            if ($unit->status !== 'occupied') {
                if ($request->has('monthly_rent')) {
                    $updateData['monthly_rent'] = $request->monthly_rent;
                }
                if ($request->has('deposit_amount')) {
                    $updateData['deposit_amount'] = $request->deposit_amount;
                }
            }

            $unit->update($updateData);

            return ApiResponse::success(
                new UnitResource($unit->load('property')),
                'Unit updated successfully'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update unit: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->hasRole('company_admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $unit = Unit::with('property')->find($id);

        if (!$unit) {
            return ApiResponse::error('Unit not found', 404);
        }

        if ($unit->property->tenant_id !== $user->tenant_id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        if ($unit->status !== 'available') {
            return ApiResponse::error('Can only delete vacant units', 400);
        }

        try {
            DB::beginTransaction();

            $unit->update(['status' => 'deleted']);

            DB::commit();

            return ApiResponse::success(null, 'Unit deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete unit: ' . $e->getMessage(), 500);
        }
    }
}
