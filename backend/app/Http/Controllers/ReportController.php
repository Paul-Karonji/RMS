<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected ReportService $reportService;
    protected ExportService $exportService;
    
    public function __construct(ReportService $reportService, ExportService $exportService)
    {
        $this->reportService = $reportService;
        $this->exportService = $exportService;
    }
    
    /**
     * Generate financial report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function financial(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'property_id' => 'nullable|uuid|exists:properties,id',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a company',
            ], 403);
        }
        
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $report = $this->reportService->generateFinancialReport(
                $tenantId,
                $startDate,
                $endDate,
                $request->property_id
            );
            
            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate financial report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate occupancy report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function occupancy(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a company',
            ], 403);
        }
        
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $report = $this->reportService->generateOccupancyReport(
                $tenantId,
                $startDate,
                $endDate
            );
            
            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate occupancy report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate payment report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payments(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,completed,failed',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a company',
            ], 403);
        }
        
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $report = $this->reportService->generatePaymentReport(
                $tenantId,
                $startDate,
                $endDate,
                $request->status
            );
            
            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payment report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate owner statement
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ownerStatement(Request $request)
    {
        $request->validate([
            'property_owner_id' => 'required|uuid|exists:property_owners,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a company',
            ], 403);
        }
        
        // Verify the property owner belongs to this tenant
        $owner = \App\Models\PropertyOwner::where('id', $request->property_owner_id)
            ->where('tenant_id', $tenantId)
            ->first();
        
        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Property owner not found or does not belong to your company',
            ], 404);
        }
        
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $report = $this->reportService->generateOwnerStatement(
                $request->property_owner_id,
                $startDate,
                $endDate
            );
            
            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate owner statement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Export report to CSV or PDF
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:financial,occupancy,payments,owner_statement',
            'format' => 'required|in:csv,pdf',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'property_id' => 'nullable|uuid|exists:properties,id',
            'property_owner_id' => 'required_if:report_type,owner_statement|uuid|exists:property_owners,id',
            'status' => 'nullable|in:pending,completed,failed',
        ]);
        
        $tenantId = auth()->user()->tenant_id;
        
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a company',
            ], 403);
        }
        
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            // Generate report data based on type
            $reportData = match($request->report_type) {
                'financial' => $this->reportService->generateFinancialReport(
                    $tenantId,
                    $startDate,
                    $endDate,
                    $request->property_id
                ),
                'occupancy' => $this->reportService->generateOccupancyReport(
                    $tenantId,
                    $startDate,
                    $endDate
                ),
                'payments' => $this->reportService->generatePaymentReport(
                    $tenantId,
                    $startDate,
                    $endDate,
                    $request->status
                ),
                'owner_statement' => $this->reportService->generateOwnerStatement(
                    $request->property_owner_id,
                    $startDate,
                    $endDate
                ),
            };
            
            $filename = $request->report_type . '_report';
            
            // Export based on format
            if ($request->format === 'csv') {
                // Prepare CSV data
                $headers = $this->getReportHeaders($request->report_type);
                $data = $this->prepareDataForCsv($reportData, $request->report_type);
                
                $filePath = $this->exportService->exportToCsv($data, $headers, $filename);
                
                return response()->download($filePath)->deleteFileAfterSend(true);
            } else {
                // Export to PDF
                $template = $request->report_type;
                $filePath = $this->exportService->exportToPdf($reportData, $template, $filename);
                
                return response()->download($filePath)->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get CSV headers for report type
     */
    private function getReportHeaders(string $reportType): array
    {
        return match($reportType) {
            'financial' => ['Property Name', 'Revenue', 'Expenses', 'Net Income'],
            'occupancy' => ['Unit Number', 'Unit Type', 'Status', 'Monthly Rent', 'Property Name'],
            'payments' => ['Date', 'Amount', 'Status', 'Payment Method', 'Tenant Name', 'Unit Number'],
            'owner_statement' => ['Date', 'Transaction Type', 'Amount', 'Description'],
            default => [],
        };
    }
    
    /**
     * Prepare data for CSV export
     */
    private function prepareDataForCsv(array $reportData, string $reportType): array
    {
        // This would need to be customized based on report structure
        // For now, return empty array - implement based on actual report structure
        return [];
    }
}
