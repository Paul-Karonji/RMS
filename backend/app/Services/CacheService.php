<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache Service
 * Centralized caching for dashboard metrics, reports, and public searches
 */
class CacheService
{
    /**
     * Cache durations in seconds
     */
    const DASHBOARD_TTL = 300; // 5 minutes
    const REPORT_TTL = 1800; // 30 minutes
    const PUBLIC_SEARCH_TTL = 600; // 10 minutes
    const STATS_TTL = 180; // 3 minutes

    /**
     * Cache dashboard metrics for a tenant
     */
    public function cacheDashboardMetrics(string $tenantId, callable $callback): array
    {
        $key = "dashboard:metrics:{$tenantId}";
        
        return Cache::remember($key, self::DASHBOARD_TTL, function () use ($callback) {
            Log::info('Cache miss: Generating dashboard metrics');
            return $callback();
        });
    }

    /**
     * Cache company dashboard data
     */
    public function cacheCompanyDashboard(string $tenantId, callable $callback): array
    {
        $key = "dashboard:company:{$tenantId}";
        
        return Cache::remember($key, self::DASHBOARD_TTL, $callback);
    }

    /**
     * Cache owner dashboard data
     */
    public function cacheOwnerDashboard(string $ownerId, callable $callback): array
    {
        $key = "dashboard:owner:{$ownerId}";
        
        return Cache::remember($key, self::DASHBOARD_TTL, $callback);
    }

    /**
     * Cache report data
     */
    public function cacheReport(string $reportType, array $params, callable $callback): array
    {
        $key = "report:{$reportType}:" . md5(json_encode($params));
        
        return Cache::remember($key, self::REPORT_TTL, $callback);
    }

    /**
     * Cache public property search results
     */
    public function cachePublicSearch(array $filters, callable $callback)
    {
        $key = "public:search:" . md5(json_encode($filters));
        
        return Cache::remember($key, self::PUBLIC_SEARCH_TTL, $callback);
    }

    /**
     * Cache statistics
     */
    public function cacheStats(string $key, callable $callback)
    {
        $cacheKey = "stats:{$key}";
        
        return Cache::remember($cacheKey, self::STATS_TTL, $callback);
    }

    /**
     * Invalidate dashboard cache for a tenant
     */
    public function invalidateDashboard(string $tenantId): void
    {
        Cache::forget("dashboard:metrics:{$tenantId}");
        Cache::forget("dashboard:company:{$tenantId}");
    }

    /**
     * Invalidate owner dashboard cache
     */
    public function invalidateOwnerDashboard(string $ownerId): void
    {
        Cache::forget("dashboard:owner:{$ownerId}");
    }

    /**
     * Invalidate all report caches
     */
    public function invalidateReports(): void
    {
        Cache::tags(['reports'])->flush();
    }

    /**
     * Invalidate public search cache
     */
    public function invalidatePublicSearch(): void
    {
        Cache::tags(['public_search'])->flush();
    }

    /**
     * Clear all caches
     */
    public function clearAll(): void
    {
        Cache::flush();
        Log::info('All caches cleared');
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'dashboard_ttl' => self::DASHBOARD_TTL,
            'report_ttl' => self::REPORT_TTL,
            'public_search_ttl' => self::PUBLIC_SEARCH_TTL,
            'stats_ttl' => self::STATS_TTL,
        ];
    }
}
