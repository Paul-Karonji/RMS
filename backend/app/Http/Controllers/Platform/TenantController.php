<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CreateTenantRequest;
use App\Http\Requests\Platform\UpdateTenantRequest;
use App\Http\Resources\Platform\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CompanyBalance;
use App\Notifications\CompanyAccountCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with(['balance', 'adminUser'])
            ->withCount(['properties', 'users']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('pricing_model')) {
            $query->where('pricing_model', $request->pricing_model);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('company_name', 'ILIKE', "%{$request->search}%")
                  ->orWhere('company_email', 'ILIKE', "%{$request->search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $tenants = $query->paginate($perPage);

        return TenantResource::collection($tenants);
    }

    public function store(CreateTenantRequest $request)
    {
        return DB::transaction(function() use ($request) {
            $tempPassword = Str::random(12) . rand(10, 99);

            $tenant = Tenant::create([
                'company_name' => $request->company_name,
                'admin_email' => $request->admin_email,
                'admin_phone' => $request->admin_phone,
                'pricing_model' => $request->pricing_model,
                
                'cashout_fee_percentage' => $request->pricing_model === 'payment_processing' 
                    ? $request->cashout_fee_percentage 
                    : null,
                'min_platform_fee_percentage' => $request->pricing_model === 'payment_processing'
                    ? $request->min_platform_fee_percentage
                    : null,
                'max_platform_fee_percentage' => $request->pricing_model === 'payment_processing'
                    ? $request->max_platform_fee_percentage
                    : null,
                'default_platform_fee_percentage' => $request->pricing_model === 'payment_processing'
                    ? $request->default_platform_fee_percentage
                    : null,
                
                'subscription_plan' => $request->pricing_model === 'listings_only'
                    ? $request->subscription_plan
                    : null,
                'subscription_amount' => $request->pricing_model === 'listings_only'
                    ? $this->getSubscriptionAmount($request->subscription_plan)
                    : null,
                'subscription_status' => 'active', // Always set to active, regardless of pricing model
                'subscription_started_at' => $request->pricing_model === 'listings_only'
                    ? now()
                    : null,
                'next_billing_date' => $request->pricing_model === 'listings_only'
                    ? $this->getNextBillingDate($request->subscription_plan)
                    : null,
                
                'status' => 'active',
                'created_by' => auth('platform')->id(),
            ]);

            $adminUser = User::create([
                'name' => $request->admin_name ?? $request->company_name . ' Admin',
                'email' => $request->admin_email,
                'phone' => $request->admin_phone,
                'password_hash' => Hash::make($tempPassword),
                'role' => 'company_admin',
                'tenant_id' => $tenant->id,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);

            $tenant->update(['admin_user_id' => $adminUser->id]);

            CompanyBalance::create([
                'tenant_id' => $tenant->id,
            ]);

            // Send email notification
            try {
                $adminUser->notify(new CompanyAccountCreated($tenant, $tempPassword));
            } catch (\Exception $e) {
                Log::error('Failed to send company creation email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data' => [
                    'tenant' => new TenantResource($tenant->load(['balance', 'adminUser'])),
                    'admin_credentials' => [
                        'email' => $adminUser->email,
                        'temporary_password' => $tempPassword,
                    ],
                ],
            ], 201);
        });
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['balance', 'adminUser', 'subscriptionInvoices'])
               ->loadCount(['properties', 'users']);
        
        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $tenant->update($request->validated());
        
        if ($request->has('subscription_plan') && $tenant->pricing_model === 'listings_only') {
            $tenant->update([
                'subscription_amount' => $this->getSubscriptionAmount($request->subscription_plan),
                'next_billing_date' => $this->getNextBillingDate($request->subscription_plan),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => new TenantResource($tenant->load(['balance', 'adminUser'])),
        ]);
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->update(['status' => 'deleted']);

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => 'Company suspended successfully',
        ]);
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Company activated successfully',
        ]);
    }

    private function getSubscriptionAmount($plan)
    {
        return match($plan) {
            'weekly' => 500.00,
            'monthly' => 1500.00,
            'annual' => 15000.00,
            default => null,
        };
    }

    private function getNextBillingDate($plan)
    {
        return match($plan) {
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'annual' => now()->addYear(),
            default => null,
        };
    }
}
