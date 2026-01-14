<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
    protected DashboardService $dashboardService;
    
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    /**
     * Get tenant dashboard data
     * Schema: leases.tenant_id → users.id (tenant renter)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get authenticated user ID (tenant renter)
        $userId = auth()->id();
        
        try {
            // Get lease information
            $leaseInfo = $this->dashboardService->getTenantLeaseInfo($userId);
            
            // Get payment summary
            $paymentSummary = $this->dashboardService->getTenantPaymentSummary($userId);
            
            // Get maintenance requests - Schema: maintenance_requests (unit_id, status, created_at, category, priority)
            $maintenanceRequests = [];
            
            if (isset($leaseInfo['lease_info']) && $leaseInfo['lease_info']) {
                $lease = \App\Models\Lease::find($leaseInfo['lease_info']['lease_id']);
                
                if ($lease) {
                    $maintenanceRequests = MaintenanceRequest::where('unit_id', $lease->unit_id)
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->map(function($request) {
                            return [
                                'id' => $request->id,
                                'category' => $request->category,
                                'priority' => $request->priority,
                                'status' => $request->status,
                                'description' => $request->description,
                                'created_at' => $request->created_at,
                            ];
                        });
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => array_merge(
                    $leaseInfo,
                    $paymentSummary,
                    [
                        'maintenance_requests' => $maintenanceRequests,
                    ]
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tenant dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
