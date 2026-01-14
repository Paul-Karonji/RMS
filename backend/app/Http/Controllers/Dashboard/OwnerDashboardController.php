<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    protected DashboardService $dashboardService;
    
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    /**
     * Get owner dashboard data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get authenticated property owner ID
        // Assuming the user has a property_owner_id or is a PropertyOwner
        $user = auth()->user();
        
        // Check if user has property_owner_id or get from PropertyOwner model
        $ownerId = $user->property_owner_id ?? $user->id;
        
        if (!$ownerId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a property owner account',
            ], 403);
        }
        
        try {
            // Get owner metrics
            $ownerMetrics = $this->dashboardService->getOwnerDashboardMetrics($ownerId);
            $propertyPerformance = $this->dashboardService->getOwnerPropertyPerformance($ownerId);
            
            return response()->json([
                'success' => true,
                'data' => array_merge($ownerMetrics, $propertyPerformance),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load owner dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
