<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Platform\Auth\PlatformLoginController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\RevenueController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyApprovalController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\Public\PublicUnitController;
use App\Http\Controllers\Api\Public\PublicRentalInquiryController;
use App\Http\Controllers\Api\Public\PublicReservationController;

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

// Public Marketplace Routes (Week 8)
Route::prefix('public')->group(function () {
    // Unit search and details
    Route::get('units', [PublicUnitController::class, 'index'])
        ->name('public.units.index');
    Route::get('units/filter-options', [PublicUnitController::class, 'filterOptions'])
        ->name('public.units.filter-options');
    Route::get('units/{id}', [PublicUnitController::class, 'show'])
        ->name('public.units.show');
    
    // Rental inquiries
    Route::post('inquiries', [PublicRentalInquiryController::class, 'store'])
        ->name('public.inquiries.store');
    Route::get('inquiries/{id}', [PublicRentalInquiryController::class, 'show'])
        ->name('public.inquiries.show');
    
    // Reservations
    Route::post('units/{unitId}/reserve', [PublicReservationController::class, 'store'])
        ->name('public.reservations.store');
    Route::get('reservations/{id}', [PublicReservationController::class, 'show'])
        ->name('public.reservations.show');
    Route::post('reservations/{id}/cancel', [PublicReservationController::class, 'cancel'])
        ->name('public.reservations.cancel');
});

// ==========================================================================
// WEBHOOK ROUTES (No authentication, signature verification in controller)
// ==========================================================================

Route::prefix('webhooks')->group(function () {
    // Stripe webhooks
    Route::post('stripe', [\App\Http\Controllers\Webhook\StripeWebhookController::class, 'handle'])
        ->name('webhooks.stripe');
    
    // M-Pesa webhooks
    Route::post('mpesa', [\App\Http\Controllers\Webhook\MpesaWebhookController::class, 'handle'])
        ->name('webhooks.mpesa');
    Route::post('mpesa/result', [\App\Http\Controllers\Webhook\MpesaWebhookController::class, 'result'])
        ->name('webhooks.mpesa.result');
    Route::post('mpesa/timeout', [\App\Http\Controllers\Webhook\MpesaWebhookController::class, 'timeout'])
        ->name('webhooks.mpesa.timeout');
});


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

// Platform Owner Authentication
Route::prefix('platform/auth')->group(function () {
    Route::post('/login', [PlatformLoginController::class, 'login'])
        ->name('platform.auth.login');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [PlatformLoginController::class, 'logout'])
            ->name('platform.auth.logout');
        Route::get('/me', [PlatformLoginController::class, 'me'])
            ->name('platform.auth.me');
    });
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

        // ==========================================================================
        // PROPERTY MANAGEMENT
        // ==========================================================================
        
        // Properties
        Route::apiResource('properties', PropertyController::class);
        Route::post('properties/{id}/resubmit', [PropertyController::class, 'resubmit'])
            ->name('properties.resubmit');
        Route::post('properties/{id}/assign-manager', [PropertyController::class, 'assignManager'])
            ->name('properties.assign-manager');
        
        // Property Approval (Admin only)
        Route::patch('properties/{id}/approve', [PropertyApprovalController::class, 'approve'])
            ->name('properties.approve');
        Route::patch('properties/{id}/reject', [PropertyApprovalController::class, 'reject'])
            ->name('properties.reject');
        
        // Units
        Route::post('properties/{property}/units', [UnitController::class, 'store'])
            ->name('units.store');
        Route::apiResource('units', UnitController::class)->except(['store']);

        // ==========================================================================
        // TENANT & LEASE MANAGEMENT (Week 9)
        // ==========================================================================
        
        // Tenants (Renters)
        Route::apiResource('tenants', \App\Http\Controllers\Api\TenantController::class);
        
        // Inquiry Management
        Route::get('inquiries', [\App\Http\Controllers\Api\InquiryApprovalController::class, 'index'])
            ->name('inquiries.index');
        Route::patch('inquiries/{id}/approve', [\App\Http\Controllers\Api\InquiryApprovalController::class, 'approve'])
            ->name('inquiries.approve');
        Route::patch('inquiries/{id}/reject', [\App\Http\Controllers\Api\InquiryApprovalController::class, 'reject'])
            ->name('inquiries.reject');
        
        // Leases
        Route::apiResource('leases', \App\Http\Controllers\Api\LeaseController::class);
        Route::patch('leases/{id}/terminate', [\App\Http\Controllers\Api\LeaseController::class, 'terminate'])
            ->name('leases.terminate');
        Route::post('leases/{id}/renew', [\App\Http\Controllers\Api\LeaseController::class, 'renew'])
            ->name('leases.renew');

        // ==========================================================================
        // PAYMENT PROCESSING (Week 11)
        // ==========================================================================
        
        // Payments
        Route::apiResource('payments', \App\Http\Controllers\PaymentController::class);
        Route::get('payments/{payment}/status', [\App\Http\Controllers\PaymentController::class, 'status'])
            ->name('payments.status');

        // ==========================================================================
        // PAYOUT SYSTEM (Week 14)
        // ==========================================================================
        
        // Cashout Requests (Company withdraws balance)
        Route::get('cashout-requests', [\App\Http\Controllers\CashoutRequestController::class, 'index'])
            ->name('cashout-requests.index');
        Route::post('cashout-requests', [\App\Http\Controllers\CashoutRequestController::class, 'store'])
            ->name('cashout-requests.store');
        Route::get('cashout-requests/{cashoutRequest}', [\App\Http\Controllers\CashoutRequestController::class, 'show'])
            ->name('cashout-requests.show');
        
        // Owner Payments (Company marks offline payments to property owners)
        Route::get('owner-payments', [\App\Http\Controllers\OwnerPaymentController::class, 'index'])
            ->name('owner-payments.index');
        Route::post('owner-payments', [\App\Http\Controllers\OwnerPaymentController::class, 'store'])
            ->name('owner-payments.store');


    });

    // ==========================================================================
    // ROLE-BASED ROUTES
    // ==========================================================================

    // Platform Owner Routes
    Route::middleware('platform.role:platform_owner')->prefix('platform')->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('platform.dashboard');
        
        // Revenue
        Route::get('revenue', [RevenueController::class, 'summary'])
            ->name('platform.revenue');
        
        // Tenants (Companies)
        Route::apiResource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])
            ->name('platform.tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])
            ->name('platform.tenants.activate');
        
        // Cashout Approvals (Week 14)
        Route::get('cashout-requests', [\App\Http\Controllers\Platform\CashoutApprovalController::class, 'index'])
            ->name('platform.cashout-requests.index');
        Route::patch('cashout-requests/{id}/approve', [\App\Http\Controllers\Platform\CashoutApprovalController::class, 'approve'])
            ->name('platform.cashout-requests.approve');
        Route::patch('cashout-requests/{id}/reject', [\App\Http\Controllers\Platform\CashoutApprovalController::class, 'reject'])
            ->name('platform.cashout-requests.reject');
        Route::patch('cashout-requests/{id}/process', [\App\Http\Controllers\Platform\CashoutApprovalController::class, 'process'])
            ->name('platform.cashout-requests.process');
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

    // ==========================================================================
    // WEEK 15: DASHBOARDS & REPORTING
    // ==========================================================================
    
    // Company Dashboard (Company Admin)
    Route::middleware(['tenant', 'role:company_admin'])->group(function () {
        Route::get('dashboard/company', [\App\Http\Controllers\Dashboard\CompanyDashboardController::class, 'index']);
    });
    
    // Owner Dashboard (Property Owner)
    Route::middleware(['tenant', 'role:property_owner'])->group(function () {
        Route::get('dashboard/owner', [\App\Http\Controllers\Dashboard\OwnerDashboardController::class, 'index']);
    });
    
    // Tenant Dashboard (Tenant Renter)
    Route::middleware(['tenant', 'role:tenant'])->group(function () {
        Route::get('dashboard/tenant', [\App\Http\Controllers\Dashboard\TenantDashboardController::class, 'index']);
    });
    
    // Reports (Company Admin)
    Route::middleware(['tenant', 'role:company_admin'])->prefix('reports')->group(function () {
        Route::get('financial', [\App\Http\Controllers\ReportController::class, 'financial']);
        Route::get('occupancy', [\App\Http\Controllers\ReportController::class, 'occupancy']);
        Route::get('payments', [\App\Http\Controllers\ReportController::class, 'payments']);
        Route::get('owner-statement', [\App\Http\Controllers\ReportController::class, 'ownerStatement']);
        Route::post('export', [\App\Http\Controllers\ReportController::class, 'export']);
    });

});
