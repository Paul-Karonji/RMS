# WEEK 3: BACKEND AUTHENTICATION IMPLEMENTATION

> **Reference Document for Context Continuity**
> Created: January 3, 2026
> Status: ✅ COMPLETED

---

## OVERVIEW

This document tracks the implementation of backend authentication for the Rental Management SaaS (RMS) platform. Week 3 focuses on Laravel Sanctum-based API authentication.

---

## CURRENT STATE (Before Week 3)

### ✅ Already Completed (Weeks 1-2)
- Laravel 12 installed with dependencies
- Laravel Sanctum installed (`laravel/sanctum: ^4.2`)
- Spatie Permissions installed (`spatie/laravel-permission: ^6.24`)
- Stripe and M-Pesa packages installed
- All 29 database migrations created
- All 28 models created
- Basic controllers exist (no auth controllers)
- Frontend React + Vite + Tailwind setup exists

### ❌ Missing (To Be Built in Week 3)
- Authentication controllers
- API routes file (`routes/api.php`)
- Form Request validators
- API Resources
- Custom middleware
- Authentication tests
- Postman collection

---

## IMPLEMENTATION TASKS

### PHASE 1: Sanctum Configuration
- [x] Verify Sanctum is installed
- [x] Check `personal_access_tokens` migration exists
- [x] Verify User model has `HasApiTokens` trait
- [x] Configure Sanctum settings if needed

### PHASE 2: Form Request Validators
Create in `app/Http/Requests/Auth/`:
- [x] `LoginRequest.php` - email, password validation
- [x] `RegisterRequest.php` - company_name, name, email, phone, password validation
- [x] `ForgotPasswordRequest.php` - email validation
- [x] `ResetPasswordRequest.php` - token, email, password validation

### PHASE 3: API Resources
Create in `app/Http/Resources/`:
- [x] `UserResource.php` - Transform user data for API
- [x] `AuthResource.php` - User + token response

### PHASE 4: Authentication Controllers
Create in `app/Http/Controllers/Auth/`:
- [x] `LoginController.php`
  - `login()` - Authenticate and return token
  - `user()` - Get authenticated user
  - `logout()` - Revoke token
- [x] `RegisterController.php`
  - `register()` - Property owner self-registration
- [x] `ForgotPasswordController.php`
  - `sendResetLink()` - Send password reset email
- [x] `ResetPasswordController.php`
  - `reset()` - Reset password with token

### PHASE 5: Custom Middleware
Create in `app/Http/Middleware/`:
- [x] `EnsureTenantContext.php` - Extract tenant_id from user
- [x] `CheckRole.php` - Verify user has required role

### PHASE 6: API Routes
Create `routes/api.php`:
```php
// Public routes
POST /api/auth/login
POST /api/auth/register
POST /api/auth/forgot-password
POST /api/auth/reset-password

// Protected routes (auth:sanctum)
GET  /api/auth/user
POST /api/auth/logout
```

### PHASE 7: API Response Helper
- [x] Create `app/Helpers/ApiResponse.php`
- [x] Standardized success/error response format

### PHASE 8: Testing
Create in `tests/Feature/Auth/`:
- [x] `LoginTest.php`
- [x] `RegisterTest.php`
- [x] `ForgotPasswordTest.php`
- [x] `ResetPasswordTest.php`

### PHASE 9: Postman Collection
- [x] Create `postman/RMS_Authentication.json`

---

## FILE STRUCTURE TO CREATE

```
backend/
├── app/
│   ├── Helpers/
│   │   └── ApiResponse.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   │       ├── LoginController.php
│   │   │       ├── RegisterController.php
│   │   │       ├── ForgotPasswordController.php
│   │   │       └── ResetPasswordController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureTenantContext.php
│   │   │   └── CheckRole.php
│   │   ├── Requests/
│   │   │   └── Auth/
│   │   │       ├── LoginRequest.php
│   │   │       ├── RegisterRequest.php
│   │   │       ├── ForgotPasswordRequest.php
│   │   │       └── ResetPasswordRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       └── AuthResource.php
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       └── Auth/
│           ├── LoginTest.php
│           ├── RegisterTest.php
│           ├── ForgotPasswordTest.php
│           └── ResetPasswordTest.php
└── postman/
    └── RMS_Authentication.json
```

---

## API ENDPOINTS SPECIFICATION

### POST /api/auth/login
**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```
**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "user@example.com",
      "role": "company_admin",
      "tenant_id": "uuid"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### POST /api/auth/register
**Request:**
```json
{
  "company_name": "ABC Properties",
  "name": "John Doe",
  "email": "john@abc.com",
  "phone": "+254712345678",
  "password": "password123",
  "password_confirmation": "password123"
}
```
**Response (201):**
```json
{
  "success": true,
  "message": "Registration successful. Please check your email.",
  "data": {
    "user": { ... },
    "token": "1|abc123..."
  }
}
```

### POST /api/auth/forgot-password
**Request:**
```json
{
  "email": "user@example.com"
}
```
**Response (200):**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

### POST /api/auth/reset-password
**Request:**
```json
{
  "token": "reset-token-here",
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```
**Response (200):**
```json
{
  "success": true,
  "message": "Password reset successful"
}
```

### GET /api/auth/user (Protected)
**Headers:** `Authorization: Bearer {token}`
**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "user@example.com",
    "role": "company_admin",
    "tenant_id": "uuid",
    "tenant": {
      "id": "uuid",
      "company_name": "ABC Properties"
    }
  }
}
```

### POST /api/auth/logout (Protected)
**Headers:** `Authorization: Bearer {token}`
**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## ERROR RESPONSE FORMAT

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

**HTTP Status Codes:**
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

---

## USER ROLES IN SYSTEM

From `rental_saas_final_arch.txt`:
1. **platform_owner** - Platform admin (uses `platform_users` table)
2. **company_admin** - Property management company admin
3. **company_staff** - Company employee
4. **property_owner** - Property owner (view-only dashboard)

---

## BUSINESS RULES

1. **Property Owner Self-Registration:**
   - Creates new tenant (company) record
   - Creates user with role 'property_owner'
   - Creates property_owner record
   - Sends welcome email

2. **Login Logic:**
   - Check if user exists
   - Verify password
   - Check user status (active/suspended)
   - Generate Sanctum token
   - Return user with role and tenant info

3. **Token Management:**
   - Tokens don't expire by default (configurable)
   - Logout revokes current token only
   - Multiple tokens allowed per user (different devices)

---

## PROGRESS LOG

| Date | Task | Status |
|------|------|--------|
| 2026-01-03 | Created reference document | ✅ |
| 2026-01-03 | Added HasApiTokens to User model | ✅ |
| 2026-01-03 | Added HasApiTokens to PlatformUser model | ✅ |
| 2026-01-03 | Added getAuthPassword() to both models | ✅ |
| 2026-01-03 | Created ApiResponse helper | ✅ |
| 2026-01-03 | Created LoginRequest validator | ✅ |
| 2026-01-03 | Created RegisterRequest validator | ✅ |
| 2026-01-03 | Created ForgotPasswordRequest validator | ✅ |
| 2026-01-03 | Created ResetPasswordRequest validator | ✅ |
| 2026-01-03 | Created UserResource | ✅ |
| 2026-01-03 | Created AuthResource | ✅ |
| 2026-01-03 | Created PlatformUserResource | ✅ |
| 2026-01-03 | Created LoginController | ✅ |
| 2026-01-03 | Created RegisterController | ✅ |
| 2026-01-03 | Created ForgotPasswordController | ✅ |
| 2026-01-03 | Created ResetPasswordController | ✅ |
| 2026-01-03 | Created EnsureTenantContext middleware | ✅ |
| 2026-01-03 | Created CheckRole middleware | ✅ |
| 2026-01-03 | Created api.php routes file | ✅ |
| 2026-01-03 | Updated bootstrap/app.php | ✅ |
| 2026-01-03 | Created password_reset_tokens migration | ✅ |
| 2026-01-03 | Created LoginTest | ✅ |
| 2026-01-03 | Created RegisterTest | ✅ |
| 2026-01-03 | Created ForgotPasswordTest | ✅ |
| 2026-01-03 | Created ResetPasswordTest | ✅ |
| 2026-01-03 | Created Postman collection | ✅ |

---

## FILES CREATED/MODIFIED

### New Files Created:
```
backend/app/Helpers/ApiResponse.php
backend/app/Http/Requests/Auth/LoginRequest.php
backend/app/Http/Requests/Auth/RegisterRequest.php
backend/app/Http/Requests/Auth/ForgotPasswordRequest.php
backend/app/Http/Requests/Auth/ResetPasswordRequest.php
backend/app/Http/Resources/AuthResource.php
backend/app/Http/Resources/PlatformUserResource.php
backend/app/Http/Controllers/Auth/LoginController.php
backend/app/Http/Controllers/Auth/RegisterController.php
backend/app/Http/Controllers/Auth/ForgotPasswordController.php
backend/app/Http/Controllers/Auth/ResetPasswordController.php
backend/app/Http/Middleware/EnsureTenantContext.php
backend/app/Http/Middleware/CheckRole.php
backend/routes/api.php
backend/database/migrations/2026_01_03_000001_create_password_reset_tokens_table.php
backend/tests/Feature/Auth/LoginTest.php
backend/tests/Feature/Auth/RegisterTest.php
backend/tests/Feature/Auth/ForgotPasswordTest.php
backend/tests/Feature/Auth/ResetPasswordTest.php
backend/postman/RMS_Authentication.json
```

### Modified Files:
```
backend/app/Models/User.php - Added HasApiTokens trait and getAuthPassword()
backend/app/Models/PlatformUser.php - Extended Authenticatable, added HasApiTokens
backend/app/Http/Resources/UserResource.php - Updated toArray() method
backend/bootstrap/app.php - Added API routes and middleware aliases
```

---

## NOTES

- Using Laravel Sanctum for API token authentication
- Multi-tenant architecture with tenant_id isolation
- Platform users (platform_owner) use separate `platform_users` table
- Regular users (company_admin, company_staff, property_owner) use `users` table
- Both User and PlatformUser use `password_hash` field instead of `password`
- Custom getAuthPassword() method returns password_hash for authentication

---

## TESTING COMMANDS

```bash
# Run all auth tests
php artisan test --filter=Auth

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run migrations
php artisan migrate

# Start server for manual testing
php artisan serve
```

---

## NEXT STEPS (Week 4)

1. Frontend Authentication Implementation
   - Create React auth context
   - Build login page
   - Build registration page
   - Build forgot/reset password pages
   - Implement token storage and API client
   - Add protected routes
