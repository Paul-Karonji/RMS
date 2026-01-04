<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\CompanyBalance;
use App\Models\PlatformRevenue;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : now()->subMonth();
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : now();

        $metrics = [
            'total_companies' => Tenant::where('status', 'active')->count(),
            'new_companies_this_month' => Tenant::whereBetween('created_at', [
                now()->startOfMonth(), 
                now()->endOfMonth()
            ])->count(),
            'suspended_companies' => Tenant::where('status', 'suspended')->count(),
            'total_properties' => Property::count(),
            'total_users' => User::count(),
        ];

        $revenueMetrics = [
            'total_revenue' => PlatformRevenue::whereBetween('created_at', [$startDate, $endDate])
                ->sum('platform_revenue_amount') ?? 0,
            'revenue_from_cashouts' => PlatformRevenue::where('revenue_source', 'cashout_fee')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('platform_revenue_amount') ?? 0,
            'revenue_from_subscriptions' => PlatformRevenue::where('revenue_source', 'subscription')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('platform_revenue_amount') ?? 0,
            'lifetime_revenue' => PlatformRevenue::sum('platform_revenue_amount') ?? 0,
        ];

        $companiesByModel = Tenant::select('pricing_model', DB::raw('count(*) as count'))
            ->where('status', 'active')
            ->groupBy('pricing_model')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->pricing_model => $item->count];
            });

        $topCompanies = CompanyBalance::with('tenant')
            ->orderBy('total_earned', 'desc')
            ->limit(10)
            ->get()
            ->map(function($balance) {
                return [
                    'company_name' => $balance->tenant->company_name ?? 'Unknown',
                    'total_earned' => $balance->total_earned,
                    'total_cashed_out' => $balance->total_cashed_out,
                    'available_balance' => $balance->available_balance,
                ];
            });

        $recentCompanies = Tenant::with('adminUser')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($tenant) {
                return [
                    'id' => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'admin_name' => $tenant->adminUser->name ?? 'N/A',
                    'pricing_model' => $tenant->pricing_model,
                    'status' => $tenant->status,
                    'created_at' => $tenant->created_at,
                ];
            });

        $monthlyGrowth = Tenant::select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => array_merge($metrics, $revenueMetrics),
                'companies_by_model' => $companiesByModel,
                'top_companies' => $topCompanies,
                'recent_companies' => $recentCompanies,
                'monthly_growth' => $monthlyGrowth,
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
            ],
        ]);
    }
}
