<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyApprovalRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyApprovalController extends Controller
{
    public function approve(PropertyApprovalRequest $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        try {
            $property->update([
                'status' => 'active',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return ApiResponse::success(
                new PropertyResource($property->load(['approvedBy', 'propertyOwner'])),
                'Property approved successfully'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to approve property: ' . $e->getMessage(), 500);
        }
    }

    public function reject(PropertyApprovalRequest $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return ApiResponse::error('Property not found', 404);
        }

        try {
            $property->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return ApiResponse::success(
                new PropertyResource($property->load('propertyOwner')),
                'Property rejected'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to reject property: ' . $e->getMessage(), 500);
        }
    }
}
