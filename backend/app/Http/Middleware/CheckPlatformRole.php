<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlatformRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth('platform')->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (auth('platform')->user()->role !== $role) {
            return response()->json([
                'error' => 'Unauthorized. Platform owner access required.'
            ], 403);
        }

        return $next($request);
    }
}
