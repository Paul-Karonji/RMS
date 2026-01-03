<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\AuthResource;
use App\Http\Resources\PlatformUserResource;
use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(LoginRequest $request)
    {
        $email = $request->email;
        $password = $request->password;

        // First, try to find a regular user
        $user = User::where('email', $email)->first();

        if ($user) {
            // Verify password
            if (!Hash::check($password, $user->password_hash)) {
                return ApiResponse::unauthorized('Invalid credentials');
            }

            // Check if user is active
            if ($user->status !== 'active') {
                return ApiResponse::forbidden('Your account is ' . $user->status . '. Please contact support.');
            }

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'first_login_at' => $user->first_login_at ?? now(),
            ]);

            // Load tenant relationship
            $user->load('tenant');

            // Generate token
            $token = $user->createToken('auth-token')->plainTextToken;

            return ApiResponse::success(
                new AuthResource($user, $token),
                'Login successful'
            );
        }

        // Try to find a platform user
        $platformUser = PlatformUser::where('email', $email)->first();

        if ($platformUser) {
            // Verify password
            if (!Hash::check($password, $platformUser->password_hash)) {
                return ApiResponse::unauthorized('Invalid credentials');
            }

            // Check if platform user is active
            if ($platformUser->status !== 'active') {
                return ApiResponse::forbidden('Your account is ' . $platformUser->status . '. Please contact support.');
            }

            // Generate token
            $token = $platformUser->createToken('auth-token')->plainTextToken;

            return ApiResponse::success([
                'user' => new PlatformUserResource($platformUser),
                'token' => $token,
                'token_type' => 'Bearer',
                'is_platform_user' => true,
            ], 'Login successful');
        }

        return ApiResponse::unauthorized('Invalid credentials');
    }

    /**
     * Get authenticated user details.
     */
    public function user(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Not authenticated');
        }

        // Check if it's a platform user or regular user
        if ($user instanceof PlatformUser) {
            return ApiResponse::success([
                'user' => new PlatformUserResource($user),
                'is_platform_user' => true,
            ]);
        }

        // Load tenant for regular users
        $user->load('tenant');

        return ApiResponse::success([
            'user' => new UserResource($user),
            'is_platform_user' => false,
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Not authenticated');
        }

        // Revoke current token
        $user->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Logout from all devices.
     */
    public function logoutAll(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Not authenticated');
        }

        // Revoke all tokens
        $user->tokens()->delete();

        return ApiResponse::success(null, 'Logged out from all devices successfully');
    }
}
