<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponse;
use App\Models\PlatformUser;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Check if the authenticated user has one of the required roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Allowed roles (comma-separated or multiple params)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Authentication required');
        }

        // Platform users have a special role check
        if ($user instanceof PlatformUser) {
            if (in_array('platform_owner', $roles) || in_array('platform_admin', $roles)) {
                return $next($request);
            }
            return ApiResponse::forbidden('You do not have permission to access this resource.');
        }

        // Check if user's role is in the allowed roles
        if (!in_array($user->role, $roles)) {
            return ApiResponse::forbidden('You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
