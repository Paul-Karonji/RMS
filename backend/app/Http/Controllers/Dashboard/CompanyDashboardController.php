<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CompanyDashboardController extends Controller
{
    protected DashboardService $dashboardService;
    
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    /**
     * Get company dashboard data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Validate date range
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        // Get authenticated user's tenant_id
        $tenantId = auth()->user()->tenant_id;
        
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a company',
            ], 403);
        }
        
        // Parse dates
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();
        
        try {
            // Get all metrics
            $companyMetrics = $this->dashboardService->getCompanyMetrics($tenantId, $startDate, $endDate);
            $occupancyMetrics = $this->dashboardService->getOccupancyMetrics($tenantId);
            $paymentMetrics = $this->dashboardService->getPaymentMetrics($tenantId, $startDate, $endDate);
            $ownerMetrics = $this->dashboardService->getOwnerMetrics($tenantId);
            $recentActivity = $this->dashboardService->getRecentActivity($tenantId, 10);
            
            return response()->json([
                'success' => true,
                'data' => array_merge(
                    $companyMetrics,
                    $occupancyMetrics,
                    $paymentMetrics,
                    $ownerMetrics,
                    $recentActivity,
                    [
                        'period' => [
                            'start_date' => $startDate->toDateString(),
                            'end_date' => $endDate->toDateString(),
                        ],
                    ]
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
