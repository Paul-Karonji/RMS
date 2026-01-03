<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ==========================================================================
// PUBLIC ROUTES (No authentication required)
// ==========================================================================

Route::prefix('auth')->group(function () {
    // Login
    Route::post('/login', [LoginController::class, 'login'])
        ->name('auth.login');

    // Register (Property Owner Self-Registration)
    Route::post('/register', [RegisterController::class, 'register'])
        ->name('auth.register');

    // Forgot Password
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->name('auth.forgot-password');

    // Reset Password
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('auth.reset-password');
});

// ==========================================================================
// PROTECTED ROUTES (Authentication required)
// ==========================================================================

Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        // Get authenticated user
        Route::get('/user', [LoginController::class, 'user'])
            ->name('auth.user');

        // Logout (current device)
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('auth.logout');

        // Logout from all devices
        Route::post('/logout-all', [LoginController::class, 'logoutAll'])
            ->name('auth.logout-all');
    });

    // ==========================================================================
    // TENANT-SCOPED ROUTES (Requires tenant context)
    // ==========================================================================

    Route::middleware('tenant')->group(function () {

        // Placeholder for future tenant-scoped routes
        // These will be added in subsequent weeks

    });

    // ==========================================================================
    // ROLE-BASED ROUTES
    // ==========================================================================

    // Platform Owner Routes
    Route::middleware('role:platform_owner,platform_admin')->prefix('platform')->group(function () {
        // Placeholder for platform owner routes
        // These will be added in subsequent weeks
    });

    // Company Admin Routes
    Route::middleware(['tenant', 'role:company_admin'])->prefix('admin')->group(function () {
        // Placeholder for company admin routes
        // These will be added in subsequent weeks
    });

    // Property Owner Routes
    Route::middleware(['tenant', 'role:property_owner'])->prefix('owner')->group(function () {
        // Placeholder for property owner routes
        // These will be added in subsequent weeks
    });

    // Tenant (Renter) Routes
    Route::middleware(['tenant', 'role:tenant'])->prefix('tenant')->group(function () {
        // Placeholder for tenant/renter routes
        // These will be added in subsequent weeks
    });

});
