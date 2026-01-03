<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponse;
use App\Models\PlatformUser;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     * Ensures that the authenticated user has a tenant context.
     * Platform users are exempt from this check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Authentication required');
        }

        // Platform users don't need tenant context
        if ($user instanceof PlatformUser) {
            return $next($request);
        }

        // Regular users must have a tenant_id
        if (!$user->tenant_id) {
            return ApiResponse::forbidden('No tenant context found. Please contact support.');
        }

        // Attach tenant_id to request for easy access in controllers
        $request->merge(['current_tenant_id' => $user->tenant_id]);

        return $next($request);
    }
}
