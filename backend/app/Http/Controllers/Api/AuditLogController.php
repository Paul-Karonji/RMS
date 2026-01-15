<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Get paginated audit logs with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'model_type', 'start_date', 'end_date']);
        $perPage = min($request->query('per_page', 50), 100);

        $logs = $this->auditLogService->getLogs(
            user: $request->user(),
            filters: $filters,
            perPage: $perPage
        );

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'model_type', 'start_date', 'end_date']);

        $logs = $this->auditLogService->exportLogs(
            user: $request->user(),
            filters: $filters
        );

        // Transform for CSV export
        $data = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->user?->name ?? 'System',
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'details' => $log->details,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => $data->count(),
        ]);
    }
}
