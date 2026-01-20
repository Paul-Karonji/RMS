# Database Schema Documentation
## Rental Management SaaS Platform

**Version:** 1.0  
**Last Updated:** January 3, 2026  
**Database:** PostgreSQL 15+  
**Total Tables:** 29

---

## Table of Contents
1. [Overview](#overview)
2. [Schema Design Principles](#schema-design-principles)
3. [Table Descriptions](#table-descriptions)
4. [Relationships](#relationships)
5. [Indexes](#indexes)
6. [Constraints](#constraints)
7. [Data Types](#data-types)
8. [Migration Files](#migration-files)

---

## Overview

The RMS database is designed as a **multi-tenant SaaS platform** for property management companies. It supports:

- Multi-tenant data isolation
- UUID-based primary keys
- Comprehensive audit trails
- Financial transaction tracking
- Property and lease management
- Payment processing integration

---

## Schema Design Principles

### 1. Multi-Tenancy
- All business tables include `tenant_id` for data isolation
- Row-level security enforced at application level
- Tenant context maintained throughout the application

### 2. UUID Primary Keys
- All tables use UUID v7 for primary keys
- Globally unique identifiers
- Better for distributed systems
- Implemented via `BaseUuidModel` trait

### 3. Soft Deletes
- Critical tables support soft deletes
- Maintains data history
- Allows for data recovery

### 4. Timestamps
- All tables include `created_at` and `updated_at`
- Automatic timestamp management via Laravel
- Audit trail for all records

### 5. Foreign Key Constraints
- Referential integrity enforced at database level
- Cascade deletes where appropriate
- Set null for optional relationships

---

## Table Descriptions

### Platform Management

#### `platform_users`
**Purpose:** Platform administrators who manage the entire system

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| name | string | No | Administrator name |
| email | string | No | Unique email address |
| password | string | No | Hashed password |
| role | string | No | Admin role |
| is_active | boolean | No | Account status |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `email`

---

#### `tenants`
**Purpose:** Property management companies using the platform

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| company_name | string | No | Company name |
| company_email | string | No | Unique company email |
| company_phone | string | No | Contact phone |
| company_address | text | No | Physical address |
| pricing_model | enum | No | payment_processing or listings_only |
| subscription_plan | string | Yes | Plan name if listings_only |
| subscription_status | string | Yes | active, inactive, suspended |
| default_platform_fee_percentage | decimal(5,2) | No | Default fee % (0-100) |
| cashout_fee_percentage | decimal(5,2) | No | Cashout fee % |
| bank_account_name | string | Yes | Bank account holder |
| bank_account_number | string | Yes | Account number |
| bank_name | string | Yes | Bank name |
| mpesa_business_shortcode | string | Yes | M-Pesa shortcode |
| status | string | No | active, inactive, suspended |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `company_email`
- INDEX on `status`

**Relationships:**
- Has many `users`
- Has many `properties`
- Has one `company_balance`
- Has many `subscription_invoices`

---

#### `company_balances`
**Purpose:** Track financial balances for each company

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| total_rent_collected | decimal(12,2) | No | Total rent received |
| platform_fees_collected | decimal(12,2) | No | Total platform fees |
| total_expenses | decimal(12,2) | No | Total expenses |
| pending_cashout | decimal(12,2) | No | Amount pending withdrawal |
| available_balance | decimal(12,2) | No | Available for cashout |
| last_cashout_date | date | Yes | Last withdrawal date |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `tenant_id`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`

---

### User Management

#### `users`
**Purpose:** Company staff members and tenants

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| name | string | No | User full name |
| email | string | No | Unique email address |
| phone | string | Yes | Contact phone |
| password | string | No | Hashed password |
| role | enum | No | company_admin, property_manager, tenant, property_owner |
| is_active | boolean | No | Account status |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `email`
- INDEX on `tenant_id`, `role`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Has many `leases` (as tenant)
- Has many `payments` (as tenant)
- Has many `maintenance_requests` (as reporter)

---

#### `property_owners`
**Purpose:** Property owners who list properties on the platform

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| name | string | No | Owner name |
| email | string | No | Unique email |
| phone | string | No | Contact phone |
| id_number | string | No | National ID (unique) |
| bank_account_name | string | Yes | Bank account holder |
| bank_account_number | string | Yes | Account number |
| bank_name | string | Yes | Bank name |
| mpesa_phone | string | Yes | M-Pesa number |
| is_active | boolean | No | Account status |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `email`
- UNIQUE on `id_number`
- INDEX on `tenant_id`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Has many `properties`
- Has one `owner_balance`
- Has many `owner_payments`

---

#### `owner_balances`
**Purpose:** Track earnings for property owners

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| property_owner_id | uuid | No | Foreign key to property_owners |
| total_rent_collected | decimal(12,2) | No | Total rent from properties |
| total_expenses | decimal(12,2) | No | Total property expenses |
| total_paid_out | decimal(12,2) | No | Total paid to owner |
| pending_balance | decimal(12,2) | No | Amount pending payment |
| last_payment_date | date | Yes | Last payment date |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `property_owner_id`
- FOREIGN KEY `property_owner_id` → `property_owners.id` (CASCADE)

**Relationships:**
- Belongs to `property_owner`

---

### Property Management

#### `properties`
**Purpose:** Property listings managed by companies

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| property_owner_id | uuid | No | Foreign key to property_owners |
| property_name | string | No | Property name |
| description | text | Yes | Property description |
| address | string | No | Street address |
| city | string | No | City |
| county | string | Yes | County/State |
| postal_code | string | Yes | Postal code |
| country | string | No | Country (default: Kenya) |
| latitude | decimal(10,8) | Yes | GPS latitude |
| longitude | decimal(11,8) | Yes | GPS longitude |
| property_type | string | No | apartment, house, commercial, etc. |
| total_units | integer | No | Total number of units |
| occupied_units | integer | No | Currently occupied |
| vacant_units | integer | No | Currently vacant |
| monthly_rental_income | decimal(15,2) | No | Total monthly income |
| status | enum | No | pending_approval, approved, rejected, active, inactive |
| approved_by | uuid | Yes | Foreign key to users |
| approved_at | timestamp | Yes | Approval timestamp |
| rejection_reason | text | Yes | Reason if rejected |
| manager_id | uuid | Yes | Foreign key to users (property manager) |
| commission_percentage | decimal(5,2) | Yes | Manager commission % |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `status`, `property_type`
- INDEX on `property_owner_id`
- INDEX on `manager_id`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `property_owner_id` → `property_owners.id` (CASCADE)
- FOREIGN KEY `approved_by` → `users.id` (SET NULL)
- FOREIGN KEY `manager_id` → `users.id` (SET NULL)

**Relationships:**
- Belongs to `tenant`
- Belongs to `property_owner`
- Belongs to `manager` (user)
- Belongs to `approved_by` (user)
- Has many `units`
- Has many `property_amenities`
- Has many `leases`
- Has many `maintenance_requests`
- Has many `expenses`

---

#### `units`
**Purpose:** Individual rental units within properties

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| property_id | uuid | No | Foreign key to properties |
| unit_number | string | No | Unit identifier |
| unit_type | string | No | studio, 1br, 2br, etc. |
| bedrooms | integer | No | Number of bedrooms |
| bathrooms | integer | No | Number of bathrooms |
| square_feet | integer | Yes | Unit size |
| floor_number | integer | Yes | Floor level |
| rent_amount | decimal(10,2) | No | Monthly rent |
| deposit_amount | decimal(10,2) | No | Security deposit |
| status | enum | No | available, occupied, reserved, maintenance |
| description | text | Yes | Unit description |
| is_featured | boolean | No | Featured listing |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `property_id`, `status`
- INDEX on `unit_type`
- FOREIGN KEY `property_id` → `properties.id` (CASCADE)

**Relationships:**
- Belongs to `property`
- Has many `unit_photos`
- Has many `leases`
- Has many `reservations`
- Has many `maintenance_requests`

---

#### `property_amenities`
**Purpose:** Features and amenities of properties

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| property_id | uuid | No | Foreign key to properties |
| amenity_name | string | No | Amenity name |
| amenity_type | string | No | Category |
| description | text | Yes | Amenity description |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `property_id`
- FOREIGN KEY `property_id` → `properties.id` (CASCADE)

**Relationships:**
- Belongs to `property`

---

#### `unit_photos`
**Purpose:** Photos of rental units

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| unit_id | uuid | No | Foreign key to units |
| photo_url | string | No | Image URL |
| photo_caption | string | Yes | Photo description |
| sort_order | integer | No | Display order |
| is_primary | boolean | No | Primary photo flag |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `unit_id`, `sort_order`
- FOREIGN KEY `unit_id` → `units.id` (CASCADE)

**Relationships:**
- Belongs to `unit`

---

### Leasing & Reservations

#### `rental_inquiries`
**Purpose:** Prospective tenant inquiries

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| unit_id | uuid | No | Foreign key to units |
| full_name | string | No | Inquirer name |
| email | string | No | Contact email |
| phone | string | No | Contact phone |
| message | text | Yes | Inquiry message |
| move_in_date | date | Yes | Desired move-in date |
| status | enum | No | new, contacted, approved, rejected |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `unit_id`, `status`
- FOREIGN KEY `unit_id` → `units.id` (CASCADE)

**Relationships:**
- Belongs to `unit`

---

#### `reservations`
**Purpose:** Unit reservations with deposit

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| unit_id | uuid | No | Foreign key to units |
| user_id | uuid | No | Foreign key to users |
| reservation_date | date | No | Reservation date |
| expiry_date | date | No | Expiration date |
| deposit_amount | decimal(10,2) | No | Deposit paid |
| status | enum | No | pending, confirmed, expired, cancelled |
| payment_status | enum | No | pending, paid, refunded |
| notes | text | Yes | Additional notes |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `unit_id`, `status`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `unit_id` → `units.id` (CASCADE)
- FOREIGN KEY `user_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Belongs to `unit`
- Belongs to `user`

---

#### `leases`
**Purpose:** Lease agreements between tenants and properties

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| property_id | uuid | No | Foreign key to properties |
| unit_id | uuid | No | Foreign key to units |
| tenant_id | uuid | No | Foreign key to users |
| lease_number | string | No | Unique lease number |
| start_date | date | No | Lease start date |
| end_date | date | No | Lease end date |
| monthly_rent | decimal(10,2) | No | Monthly rent amount |
| deposit_amount | decimal(10,2) | No | Security deposit |
| payment_type | enum | No | recurring, manual |
| payment_day | integer | No | Day of month for payment |
| status | enum | No | draft, active, expired, terminated, renewed |
| terms_conditions | text | Yes | Lease terms |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `lease_number`
- INDEX on `unit_id`, `status`
- INDEX on `tenant_id`
- INDEX on `property_id`
- FOREIGN KEY `property_id` → `properties.id` (CASCADE)
- FOREIGN KEY `unit_id` → `units.id` (CASCADE)
- FOREIGN KEY `tenant_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `property`
- Belongs to `unit`
- Belongs to `tenant` (user)
- Has many `payments`
- Has many `lease_signatures`

---

#### `lease_signatures`
**Purpose:** Digital signatures for lease agreements

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| lease_id | uuid | No | Foreign key to leases |
| user_id | uuid | No | Foreign key to users |
| signer_type | enum | No | tenant, owner |
| signature_data | text | Yes | Signature image data |
| signed_at | timestamp | Yes | Signature timestamp |
| ip_address | string | Yes | Signer IP address |
| user_agent | text | Yes | Browser user agent |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `lease_id`
- FOREIGN KEY `lease_id` → `leases.id` (CASCADE)
- FOREIGN KEY `user_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `lease`
- Belongs to `user`

---

### Financial Management

#### `payments`
**Purpose:** Rent and other payments from tenants

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| lease_id | uuid | No | Foreign key to leases |
| tenant_id | uuid | No | Foreign key to users |
| payment_type | enum | No | rent, deposit, late_fee, etc. |
| amount | decimal(10,2) | No | Payment amount |
| payment_method | enum | No | mpesa, stripe, bank_transfer |
| payment_date | date | No | Payment date |
| due_date | date | No | Due date |
| status | enum | No | pending, completed, failed, refunded |
| transaction_id | string | Yes | External transaction ID |
| stripe_payment_intent | string | Yes | Stripe payment intent ID |
| mpesa_receipt | string | Yes | M-Pesa receipt number |
| notes | text | Yes | Payment notes |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `lease_id`, `status`
- INDEX on `tenant_id`, `payment_date`
- INDEX on `transaction_id`
- FOREIGN KEY `lease_id` → `leases.id` (CASCADE)
- FOREIGN KEY `tenant_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `lease`
- Belongs to `tenant` (user)
- Has many `platform_fees`
- Has many `balance_transactions`

---

#### `payment_methods`
**Purpose:** Saved payment methods for users

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| user_id | uuid | No | Foreign key to users |
| method_type | enum | No | card, mobile_money, bank_account |
| provider | string | No | stripe, mpesa, etc. |
| account_number | string | Yes | Account/phone number |
| account_name | string | Yes | Account holder name |
| card_last_four | string | Yes | Last 4 digits of card |
| card_expiry | string | Yes | Card expiry date |
| mpesa_phone | string | Yes | M-Pesa phone number |
| stripe_customer_id | string | Yes | Stripe customer ID |
| stripe_payment_method_id | string | Yes | Stripe payment method ID |
| is_default | boolean | No | Default payment method |
| is_active | boolean | No | Active status |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `user_id`, `is_active`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `user_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Belongs to `user`

---

#### `platform_fees`
**Purpose:** Platform fees collected from payments

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| payment_id | uuid | No | Foreign key to payments |
| property_id | uuid | No | Foreign key to properties |
| fee_type | enum | No | rent_fee, cashout_fee |
| fee_percentage | decimal(5,2) | No | Fee percentage |
| fee_amount | decimal(10,2) | No | Calculated fee amount |
| base_amount | decimal(10,2) | No | Amount fee is based on |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `fee_type`
- INDEX on `payment_id`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `payment_id` → `payments.id` (CASCADE)
- FOREIGN KEY `property_id` → `properties.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Belongs to `payment`
- Belongs to `property`

---

#### `balance_transactions`
**Purpose:** Audit trail for all financial movements

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| payment_id | uuid | Yes | Foreign key to payments |
| property_owner_id | uuid | Yes | Foreign key to property_owners |
| transaction_type | enum | No | payment_received, cashout, owner_payment, etc. |
| amount | decimal(12,2) | No | Transaction amount |
| fee_amount | decimal(12,2) | No | Fee charged |
| net_amount | decimal(12,2) | No | Net amount after fees |
| transaction_date | date | No | Transaction date |
| description | text | Yes | Transaction description |
| reference_id | string | Yes | External reference |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `transaction_type`, `created_at`
- INDEX on `property_owner_id`, `transaction_type`, `created_at`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `property_owner_id` → `property_owners.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Belongs to `payment` (optional)
- Belongs to `property_owner` (optional)

---

#### `cashout_requests`
**Purpose:** Company withdrawal requests

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| amount | decimal(12,2) | No | Requested amount |
| fee_amount | decimal(12,2) | No | Platform fee |
| net_amount | decimal(12,2) | No | Amount after fee |
| status | enum | No | pending, approved, rejected, processed |
| payment_method | string | No | bank_transfer, mpesa |
| payment_details | json | Yes | Payment details |
| approved_by | uuid | Yes | Foreign key to platform_users |
| approved_at | timestamp | Yes | Approval timestamp |
| processed_at | timestamp | Yes | Processing timestamp |
| transaction_id | string | Yes | External transaction ID |
| rejection_reason | text | Yes | Reason if rejected |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `status`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `approved_by` → `platform_users.id` (SET NULL)

**Relationships:**
- Belongs to `tenant`
- Belongs to `approved_by` (platform_user)

---

#### `owner_payments`
**Purpose:** Payments made to property owners

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | Yes | Foreign key to tenants |
| property_owner_id | uuid | No | Foreign key to property_owners |
| amount | decimal(12,2) | No | Payment amount |
| payment_date | date | No | Payment date |
| payment_method | string | No | bank_transfer, mpesa, cash |
| reference_number | string | Yes | Payment reference |
| notes | text | Yes | Payment notes |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `property_owner_id`, `payment_date`
- FOREIGN KEY `tenant_id` → `tenants.id` (SET NULL)
- FOREIGN KEY `property_owner_id` → `property_owners.id` (CASCADE)

**Relationships:**
- Belongs to `tenant` (optional)
- Belongs to `property_owner`

---

### Operations

#### `expenses`
**Purpose:** Property-related expenses

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| property_id | uuid | No | Foreign key to properties |
| unit_id | uuid | Yes | Foreign key to units |
| maintenance_request_id | uuid | Yes | Foreign key to maintenance_requests |
| category | string | No | utilities, repairs, insurance, etc. |
| description | string | No | Expense description |
| amount | decimal(10,2) | No | Expense amount |
| expense_date | date | No | Expense date |
| owner_share | decimal(5,2) | No | Owner's share (0-1) |
| platform_share | decimal(5,2) | No | Platform's share (0-1) |
| status | enum | No | pending, approved, rejected |
| approved_by | uuid | Yes | Foreign key to users |
| approved_at | timestamp | Yes | Approval timestamp |
| rejected_by | uuid | Yes | Foreign key to users |
| rejected_at | timestamp | Yes | Rejection timestamp |
| rejection_reason | text | Yes | Reason if rejected |
| created_by | uuid | No | Foreign key to users |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `status`, `expense_date`
- INDEX on `property_id`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `property_id` → `properties.id` (CASCADE)
- FOREIGN KEY `unit_id` → `units.id` (CASCADE)
- FOREIGN KEY `maintenance_request_id` → `maintenance_requests.id` (SET NULL)
- FOREIGN KEY `approved_by` → `users.id` (SET NULL)
- FOREIGN KEY `rejected_by` → `users.id` (SET NULL)
- FOREIGN KEY `created_by` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`
- Belongs to `property`
- Belongs to `unit` (optional)
- Belongs to `maintenance_request` (optional)
- Belongs to `approved_by` (user)
- Belongs to `rejected_by` (user)
- Belongs to `created_by` (user)

---

#### `maintenance_requests`
**Purpose:** Maintenance and repair requests

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| property_id | uuid | No | Foreign key to properties |
| unit_id | uuid | No | Foreign key to units |
| reported_by | uuid | No | Foreign key to users |
| category | string | No | plumbing, electrical, hvac, etc. |
| title | string | No | Request title |
| description | text | No | Detailed description |
| priority | enum | No | low, medium, high, urgent |
| status | enum | No | open, assigned, in_progress, completed, cancelled |
| assigned_to | uuid | Yes | Foreign key to users |
| assigned_at | timestamp | Yes | Assignment timestamp |
| completed_at | timestamp | Yes | Completion timestamp |
| completion_notes | text | Yes | Completion notes |
| estimated_cost | decimal(10,2) | Yes | Estimated repair cost |
| actual_cost | decimal(10,2) | Yes | Actual repair cost |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `property_id`, `status`
- INDEX on `unit_id`
- INDEX on `assigned_to`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `property_id` → `properties.id` (CASCADE)
- FOREIGN KEY `unit_id` → `units.id` (CASCADE)
- FOREIGN KEY `reported_by` → `users.id` (CASCADE)
- FOREIGN KEY `assigned_to` → `users.id` (SET NULL)

**Relationships:**
- Belongs to `tenant`
- Belongs to `property`
- Belongs to `unit`
- Belongs to `reported_by` (user)
- Belongs to `assigned_to` (user)
- Has many `maintenance_updates`
- Has many `expenses`

---

#### `maintenance_updates`
**Purpose:** Activity log for maintenance requests

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| maintenance_request_id | uuid | No | Foreign key to maintenance_requests |
| updated_by | uuid | No | Foreign key to users |
| update_type | enum | No | status_change, assignment, note, completion |
| description | text | Yes | Update description |
| status_before | string | Yes | Previous status |
| status_after | string | Yes | New status |
| assigned_to | uuid | Yes | Foreign key to users |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `maintenance_request_id`, `created_at`
- FOREIGN KEY `maintenance_request_id` → `maintenance_requests.id` (CASCADE)
- FOREIGN KEY `updated_by` → `users.id` (CASCADE)
- FOREIGN KEY `assigned_to` → `users.id` (SET NULL)

**Relationships:**
- Belongs to `maintenance_request`
- Belongs to `updated_by` (user)
- Belongs to `assigned_to` (user)

---

### System Tables

#### `notifications`
**Purpose:** User notifications

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | Yes | Foreign key to tenants |
| user_id | uuid | Yes | Foreign key to users |
| type | string | No | Notification type |
| title | string | No | Notification title |
| message | text | No | Notification message |
| data | json | Yes | Additional data |
| read_at | timestamp | Yes | Read timestamp |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `user_id`, `read_at`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `user_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `tenant` (optional)
- Belongs to `user` (optional)

---

#### `audit_logs`
**Purpose:** System audit trail

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | Yes | Foreign key to tenants |
| user_id | uuid | Yes | Foreign key to users |
| action | string | No | Action performed |
| model_type | string | Yes | Model class name |
| model_id | uuid | Yes | Model ID |
| details | text | Yes | Action details |
| old_values | json | Yes | Previous values |
| new_values | json | Yes | New values |
| ip_address | string | Yes | User IP address |
| user_agent | text | Yes | Browser user agent |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `tenant_id`, `created_at`
- INDEX on `user_id`, `created_at`
- INDEX on `model_type`, `model_id`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)
- FOREIGN KEY `user_id` → `users.id` (CASCADE)

**Relationships:**
- Belongs to `tenant` (optional)
- Belongs to `user` (optional)

---

#### `subscription_invoices`
**Purpose:** Subscription billing records

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid | No | Primary key |
| tenant_id | uuid | No | Foreign key to tenants |
| invoice_number | string | No | Unique invoice number |
| period_start | date | No | Billing period start |
| period_end | date | No | Billing period end |
| subscription_plan | string | Yes | Plan name |
| amount | decimal(10,2) | No | Invoice amount |
| status | enum | No | pending, paid, overdue, cancelled |
| paid_at | timestamp | Yes | Payment timestamp |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `invoice_number`
- INDEX on `tenant_id`, `status`
- FOREIGN KEY `tenant_id` → `tenants.id` (CASCADE)

**Relationships:**
- Belongs to `tenant`

---

## Relationships Summary

### One-to-One (1:1)
- `tenants` ↔ `company_balances`
- `property_owners` ↔ `owner_balances`

### One-to-Many (1:N)
- `tenants` → `users`
- `tenants` → `properties`
- `tenants` → `subscription_invoices`
- `property_owners` → `properties`
- `properties` → `units`
- `properties` → `property_amenities`
- `properties` → `leases`
- `properties` → `maintenance_requests`
- `properties` → `expenses`
- `units` → `unit_photos`
- `units` → `leases`
- `units` → `reservations`
- `leases` → `payments`
- `leases` → `lease_signatures`
- `maintenance_requests` → `maintenance_updates`
- `maintenance_requests` → `expenses`

---

## Migration Files

All migration files are located in: `backend/database/migrations/`

### Execution Order
Migrations are executed in chronological order based on timestamp:

1. `0001_01_01_000001_create_cache_table.php`
2. `0001_01_01_000002_create_jobs_table.php`
3. `2026_01_02_002135_create_personal_access_tokens_table.php`
4. `2026_01_02_215555_create_platform_users_table.php`
5. `2026_01_02_215604_create_tenants_table.php`
6. `2026_01_02_215614_create_company_balances_table.php`
7. `2026_01_02_215621_create_subscription_invoices_table.php`
8. `2026_01_02_215842_create_users_table.php`
9. `2026_01_02_215849_create_property_owners_table.php`
10. `2026_01_02_215856_create_owner_balances_table.php`
11. `2026_01_02_215925_create_properties_table.php`
12. `2026_01_02_215938_create_units_table.php`
13. `2026_01_02_215946_create_property_amenities_table.php`
14. `2026_01_02_215952_create_unit_photos_table.php`
15. `2026_01_02_220039_create_leases_table.php`
16. `2026_01_02_220048_create_lease_signatures_table.php`
17. `2026_01_02_220059_create_rental_inquiries_table.php`
18. `2026_01_02_220106_create_reservations_table.php`
19. `2026_01_02_220258_create_payments_table.php`
20. `2026_01_02_220307_create_payment_methods_table.php`
21. `2026_01_02_220314_create_platform_fees_table.php`
22. `2026_01_02_220321_create_balance_transactions_table.php`
23. `2026_01_02_220417_create_maintenance_requests_table.php`
24. `2026_01_02_220418_create_expenses_table.php`
25. `2026_01_02_220427_create_maintenance_updates_table.php`
26. `2026_01_02_220602_create_notifications_table.php`
27. `2026_01_02_220608_create_audit_logs_table.php`
28. `2026_01_02_220616_create_cashout_requests_table.php`
29. `2026_01_02_220628_create_owner_payments_table.php`

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Fresh migration (drop all tables and re-migrate)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Rollback last migration batch
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset
```

---

## Verification Commands

### Verify Relationships
```bash
php artisan db:verify-relationships
```

### Verify Data Integrity
```bash
php artisan db:verify-integrity
```

---

## Notes

1. **UUID Implementation**: All models extend `BaseUuidModel` for automatic UUID generation
2. **Multi-Tenancy**: Enforced via `BelongsToTenant` trait on applicable models
3. **Soft Deletes**: Implemented on critical tables for data recovery
4. **Timestamps**: Automatically managed by Laravel's Eloquent ORM
5. **Foreign Keys**: All relationships have proper foreign key constraints
6. **Indexes**: Strategic indexes for query performance optimization

---

**End of Documentation**
