<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Tenant;
use App\Models\PropertyOwner;
use App\Models\CompanyBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Handle property owner self-registration.
     * This creates a new tenant (company) and user with property_owner role.
     */
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create the tenant (company)
            $tenant = Tenant::create([
                'company_name' => $request->company_name,
                'admin_email' => $request->email,
                'admin_phone' => $request->phone,
                'pricing_model' => 'payment_processing', // Default pricing model
                'status' => 'active',
            ]);

            // Create the user
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password_hash' => Hash::make($request->password),
                'role' => 'property_owner',
                'account_type' => 'property_owner',
                'status' => 'active',
                'must_change_password' => false,
            ]);

            // Update tenant with admin user
            $tenant->update(['admin_user_id' => $user->id]);

            // Create property owner record
            $propertyOwner = new PropertyOwner([
                'user_id' => $user->id,
                'owner_name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 'active',
            ]);
            $propertyOwner->tenant_id = $tenant->id;
            $propertyOwner->save();

            // Create company balance record
            $companyBalance = new CompanyBalance([
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_collected' => 0,
            ]);
            $companyBalance->tenant_id = $tenant->id;
            $companyBalance->save();

            DB::commit();

            // Load tenant relationship
            $user->load('tenant');

            // Generate token
            $token = $user->createToken('auth-token')->plainTextToken;

            return ApiResponse::created(
                new AuthResource($user, $token),
                'Registration successful. Welcome to RMS!'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            return ApiResponse::serverError('Registration failed. Please try again. ' . $e->getMessage());
        }
    }
}
