<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformRevenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueController extends Controller
{
    public function summary(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : now()->subMonth();
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : now();

        $totalRevenue = PlatformRevenue::whereBetween('created_at', [$startDate, $endDate])
            ->sum('platform_revenue_amount') ?? 0;

        $revenueBySource = PlatformRevenue::select(
                'revenue_source',
                DB::raw('SUM(platform_revenue_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('revenue_source')
            ->get();

        $monthlyTrend = PlatformRevenue::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('SUM(platform_revenue_amount) as revenue'),
                'revenue_source'
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month', 'revenue_source')
            ->orderBy('month')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->month)->format('Y-m');
            });

        $revenueByCompany = PlatformRevenue::with('tenant')
            ->select(
                'tenant_id',
                DB::raw('SUM(platform_revenue_amount) as total'),
                DB::raw('COUNT(*) as transactions')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get()
            ->map(function($item) {
                return [
                    'company_name' => $item->tenant->company_name ?? 'Unknown',
                    'total_revenue' => $item->total,
                    'transactions' => $item->transactions,
                ];
            });

        $avgRevenuePerCompany = PlatformRevenue::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(platform_revenue_amount) as average')
            ->value('average') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => $totalRevenue,
                'average_revenue_per_company' => $avgRevenuePerCompany,
                'revenue_by_source' => $revenueBySource,
                'monthly_trend' => $monthlyTrend,
                'revenue_by_company' => $revenueByCompany,
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
            ],
        ]);
    }
}
