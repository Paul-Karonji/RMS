# Rental Management SaaS - Database Entity Relationship Diagram

## Overview
This document provides a comprehensive view of the database schema for the Rental Management SaaS platform, including all tables, columns, relationships, and constraints.

---

## Database Statistics
- **Total Tables**: 29
- **Core Business Tables**: 20
- **System Tables**: 9
- **Database Type**: PostgreSQL
- **Primary Key Strategy**: UUID v7

---

## Table Categories

### 1. Platform & Tenant Management
- `platform_users` - Platform administrators
- `tenants` - Property management companies
- `company_balances` - Company financial tracking
- `subscription_invoices` - Subscription billing records

### 2. User Management
- `users` - Company staff and tenants
- `property_owners` - Property owners
- `owner_balances` - Owner financial tracking

### 3. Property Management
- `properties` - Property listings
- `units` - Individual rental units
- `property_amenities` - Property features
- `unit_photos` - Unit images

### 4. Leasing & Reservations
- `leases` - Lease agreements
- `lease_signatures` - Digital signatures
- `rental_inquiries` - Prospective tenant inquiries
- `reservations` - Unit reservations

### 5. Financial Management
- `payments` - Rent payments
- `payment_methods` - Saved payment methods
- `platform_fees` - Platform fee records
- `balance_transactions` - Financial audit trail
- `cashout_requests` - Company withdrawal requests
- `owner_payments` - Owner payout records

### 6. Operations
- `expenses` - Property expenses
- `maintenance_requests` - Maintenance tickets
- `maintenance_updates` - Maintenance activity log

### 7. System
- `notifications` - User notifications
- `audit_logs` - System audit trail
- `personal_access_tokens` - API tokens
- `cache` - Application cache
- `jobs` - Queue jobs

---

## Detailed Table Schemas

### Platform & Tenant Management

#### platform_users
```
┌─────────────────────────────────────────┐
│          platform_users                 │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│     name (string)                       │
│     email (string, unique)              │
│     password (string)                   │
│     role (string)                       │
│     is_active (boolean)                 │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
```

#### tenants
```
┌─────────────────────────────────────────┐
│             tenants                     │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│     company_name (string)               │
│     company_email (string, unique)      │
│     company_phone (string)              │
│     company_address (text)              │
│     pricing_model (enum)                │
│     subscription_plan (string, null)    │
│     subscription_status (string, null)  │
│     default_platform_fee_% (decimal)    │
│     cashout_fee_percentage (decimal)    │
│     bank_account_name (string, null)    │
│     bank_account_number (string, null)  │
│     bank_name (string, null)            │
│     mpesa_business_shortcode (str, null)│
│     is_active (boolean)                 │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
```

#### company_balances
```
┌─────────────────────────────────────────┐
│         company_balances                │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│     total_rent_collected (decimal)      │
│     platform_fees_collected (decimal)   │
│     total_expenses (decimal)            │
│     pending_cashout (decimal)           │
│     available_balance (decimal)         │
│     last_cashout_date (date, null)      │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> tenants (1:1)
```

#### subscription_invoices
```
┌─────────────────────────────────────────┐
│       subscription_invoices             │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│     invoice_number (string, unique)     │
│     period_start (date)                 │
│     period_end (date)                   │
│     subscription_plan (string, null)    │
│     amount (decimal)                    │
│     status (string)                     │
│     paid_at (timestamp, null)           │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> tenants (N:1)
```

---

### User Management

#### users
```
┌─────────────────────────────────────────┐
│              users                      │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│     name (string)                       │
│     email (string, unique)              │
│     phone (string, null)                │
│     password (string)                   │
│     role (enum: admin, manager, tenant) │
│     is_active (boolean)                 │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> tenants (N:1)
```

#### property_owners
```
┌─────────────────────────────────────────┐
│          property_owners                │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│     name (string)                       │
│     email (string, unique)              │
│     phone (string)                      │
│     id_number (string, unique)          │
│     bank_account_name (string, null)    │
│     bank_account_number (string, null)  │
│     bank_name (string, null)            │
│     mpesa_phone (string, null)          │
│     is_active (boolean)                 │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> tenants (N:1)
```

#### owner_balances
```
┌─────────────────────────────────────────┐
│          owner_balances                 │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  property_owner_id → property_owners │
│     total_rent_collected (decimal)      │
│     total_expenses (decimal)            │
│     total_paid_out (decimal)            │
│     pending_balance (decimal)           │
│     last_payment_date (date, null)      │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> property_owners (1:1)
```

---

### Property Management

#### properties
```
┌─────────────────────────────────────────┐
│            properties                   │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  owner_id → property_owners.id      │
│ FK  property_manager_id → users.id(null)│
│     property_name (string)              │
│     property_type (string)              │
│     address (text)                      │
│     city (string)                       │
│     county (string)                     │
│     description (text, null)            │
│     total_units (integer)               │
│     approval_status (enum)              │
│     approval_notes (text, null)         │
│ FK  approved_by → users.id (null)      │
│     approved_at (timestamp, null)       │
│     fee_type (enum)                     │
│     fee_value (decimal)                 │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     ├──> property_owners (N:1)
     ├──> users (property_manager) (N:1)
     └──> users (approved_by) (N:1)
```

#### units
```
┌─────────────────────────────────────────┐
│              units                      │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  property_id → properties.id        │
│     unit_number (string)                │
│     unit_type (string)                  │
│     bedrooms (integer)                  │
│     bathrooms (integer)                 │
│     square_feet (integer, null)         │
│     floor_number (integer, null)        │
│     rent_amount (decimal)               │
│     deposit_amount (decimal)            │
│     status (enum)                       │
│     description (text, null)            │
│     is_featured (boolean)               │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> properties (N:1)
```

#### property_amenities
```
┌─────────────────────────────────────────┐
│        property_amenities               │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  property_id → properties.id        │
│     amenity_name (string)               │
│     amenity_type (string)               │
│     description (text, null)            │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> properties (N:1)
```

#### unit_photos
```
┌─────────────────────────────────────────┐
│           unit_photos                   │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  unit_id → units.id                 │
│     photo_url (string)                  │
│     photo_caption (string, null)        │
│     sort_order (integer)                │
│     is_primary (boolean)                │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> units (N:1)
```

---

### Leasing & Reservations

#### rental_inquiries
```
┌─────────────────────────────────────────┐
│         rental_inquiries                │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  unit_id → units.id                 │
│     full_name (string)                  │
│     email (string)                      │
│     phone (string)                      │
│     message (text, null)                │
│     move_in_date (date, null)           │
│     status (enum)                       │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     └──> units (N:1)
```

#### reservations
```
┌─────────────────────────────────────────┐
│           reservations                  │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  unit_id → units.id                 │
│ FK  user_id → users.id                 │
│     reservation_date (date)             │
│     expiry_date (date)                  │
│     deposit_amount (decimal)            │
│     status (enum)                       │
│     payment_status (enum)               │
│     notes (text, null)                  │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     ├──> units (N:1)
     └──> users (N:1)
```

#### leases
```
┌─────────────────────────────────────────┐
│              leases                     │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  property_id → properties.id        │
│ FK  unit_id → units.id                 │
│ FK  tenant_id → users.id               │
│     lease_number (string, unique)       │
│     start_date (date)                   │
│     end_date (date)                     │
│     monthly_rent (decimal)              │
│     deposit_amount (decimal)            │
│     payment_type (enum)                 │
│     payment_day (integer)               │
│     status (enum)                       │
│     terms_conditions (text, null)       │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> properties (N:1)
     ├──> units (N:1)
     └──> users (tenant) (N:1)
```

#### lease_signatures
```
┌─────────────────────────────────────────┐
│         lease_signatures                │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  lease_id → leases.id               │
│ FK  user_id → users.id                 │
│     signer_type (enum: tenant, owner)   │
│     signature_data (text, null)         │
│     signed_at (timestamp, null)         │
│     ip_address (string, null)           │
│     user_agent (text, null)             │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> leases (N:1)
     └──> users (N:1)
```

---

### Financial Management

#### payments
```
┌─────────────────────────────────────────┐
│             payments                    │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  lease_id → leases.id               │
│ FK  tenant_id → users.id               │
│     payment_type (enum)                 │
│     amount (decimal)                    │
│     payment_method (enum)               │
│     payment_date (date)                 │
│     due_date (date)                     │
│     status (enum)                       │
│     transaction_id (string, null)       │
│     stripe_payment_intent (string, null)│
│     mpesa_receipt (string, null)        │
│     notes (text, null)                  │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> leases (N:1)
     └──> users (tenant) (N:1)
```

#### payment_methods
```
┌─────────────────────────────────────────┐
│         payment_methods                 │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  user_id → users.id                 │
│     method_type (enum)                  │
│     provider (string)                   │
│     account_number (string, null)       │
│     account_name (string, null)         │
│     card_last_four (string, null)       │
│     card_expiry (string, null)          │
│     mpesa_phone (string, null)          │
│     stripe_customer_id (string, null)   │
│     stripe_payment_method_id (str, null)│
│     is_default (boolean)                │
│     is_active (boolean)                 │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     └──> users (N:1)
```

#### platform_fees
```
┌─────────────────────────────────────────┐
│          platform_fees                  │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  payment_id → payments.id           │
│ FK  property_id → properties.id        │
│     fee_type (enum)                     │
│     fee_percentage (decimal)            │
│     fee_amount (decimal)                │
│     base_amount (decimal)               │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     ├──> payments (N:1)
     └──> properties (N:1)
```

#### balance_transactions
```
┌─────────────────────────────────────────┐
│       balance_transactions              │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  payment_id → payments.id (null)    │
│ FK  property_owner_id → prop_owners(null)│
│     transaction_type (enum)             │
│     amount (decimal)                    │
│     fee_amount (decimal)                │
│     net_amount (decimal)                │
│     transaction_date (date)             │
│     description (text, null)            │
│     reference_id (string, null)         │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     ├──> payments (N:1)
     └──> property_owners (N:1)
```

#### cashout_requests
```
┌─────────────────────────────────────────┐
│         cashout_requests                │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│     amount (decimal)                    │
│     fee_amount (decimal)                │
│     net_amount (decimal)                │
│     status (enum)                       │
│     payment_method (string)             │
│     payment_details (json, null)        │
│ FK  approved_by → platform_users (null)│
│     approved_at (timestamp, null)       │
│     processed_at (timestamp, null)      │
│     transaction_id (string, null)       │
│     rejection_reason (text, null)       │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     └──> platform_users (approved_by) (N:1)
```

#### owner_payments
```
┌─────────────────────────────────────────┐
│          owner_payments                 │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id (null)      │
│ FK  property_owner_id → prop_owners.id │
│     amount (decimal)                    │
│     payment_date (date)                 │
│     payment_method (string)             │
│     reference_number (string, null)     │
│     notes (text, null)                  │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     └──> property_owners (N:1)
```

---

### Operations

#### expenses
```
┌─────────────────────────────────────────┐
│             expenses                    │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  property_id → properties.id        │
│ FK  unit_id → units.id (null)          │
│ FK  maintenance_request_id → maint(null)│
│     category (string)                   │
│     description (string)                │
│     amount (decimal)                    │
│     expense_date (date)                 │
│     owner_share (decimal)               │
│     platform_share (decimal)            │
│     status (enum)                       │
│ FK  approved_by → users.id (null)      │
│     approved_at (timestamp, null)       │
│ FK  rejected_by → users.id (null)      │
│     rejected_at (timestamp, null)       │
│     rejection_reason (text, null)       │
│ FK  created_by → users.id              │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     ├──> properties (N:1)
     ├──> units (N:1)
     ├──> maintenance_requests (N:1)
     ├──> users (approved_by) (N:1)
     ├──> users (rejected_by) (N:1)
     └──> users (created_by) (N:1)
```

#### maintenance_requests
```
┌─────────────────────────────────────────┐
│       maintenance_requests              │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id             │
│ FK  property_id → properties.id        │
│ FK  unit_id → units.id                 │
│ FK  reported_by → users.id             │
│     category (string)                   │
│     title (string)                      │
│     description (text)                  │
│     priority (enum)                     │
│     status (enum)                       │
│ FK  assigned_to → users.id (null)      │
│     assigned_at (timestamp, null)       │
│     completed_at (timestamp, null)      │
│     completion_notes (text, null)       │
│     estimated_cost (decimal, null)      │
│     actual_cost (decimal, null)         │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     ├──> properties (N:1)
     ├──> units (N:1)
     ├──> users (reported_by) (N:1)
     └──> users (assigned_to) (N:1)
```

#### maintenance_updates
```
┌─────────────────────────────────────────┐
│       maintenance_updates               │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  maintenance_request_id → maint.id  │
│ FK  updated_by → users.id              │
│     update_type (enum)                  │
│     description (text, null)            │
│     status_before (string, null)        │
│     status_after (string, null)         │
│ FK  assigned_to → users.id (null)      │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> maintenance_requests (N:1)
     ├──> users (updated_by) (N:1)
     └──> users (assigned_to) (N:1)
```

---

### System Tables

#### notifications
```
┌─────────────────────────────────────────┐
│          notifications                  │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id (null)      │
│ FK  user_id → users.id (null)          │
│     type (string)                       │
│     title (string)                      │
│     message (text)                      │
│     data (json, null)                   │
│     read_at (timestamp, null)           │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     └──> users (N:1)
```

#### audit_logs
```
┌─────────────────────────────────────────┐
│            audit_logs                   │
├─────────────────────────────────────────┤
│ PK  id (uuid)                          │
│ FK  tenant_id → tenants.id (null)      │
│ FK  user_id → users.id (null)          │
│     action (string)                     │
│     model_type (string, null)           │
│     model_id (uuid, null)               │
│     details (text, null)                │
│     old_values (json, null)             │
│     new_values (json, null)             │
│     ip_address (string, null)           │
│     user_agent (text, null)             │
│     created_at (timestamp)              │
│     updated_at (timestamp)              │
└─────────────────────────────────────────┘
     │
     ├──> tenants (N:1)
     └──> users (N:1)
```

---

## Relationship Summary

### One-to-One (1:1)
- `tenants` ↔ `company_balances`
- `property_owners` ↔ `owner_balances`

### One-to-Many (1:N)
- `tenants` → `users`
- `tenants` → `properties`
- `tenants` → `subscription_invoices`
- `properties` → `units`
- `properties` → `property_amenities`
- `units` → `unit_photos`
- `units` → `leases`
- `leases` → `payments`
- `leases` → `lease_signatures`
- `properties` → `maintenance_requests`
- `maintenance_requests` → `maintenance_updates`
- `maintenance_requests` → `expenses`

### Many-to-One (N:1)
- All foreign key relationships listed above

---

## Indexes

### Primary Indexes
All tables use UUID primary keys with automatic indexing.

### Foreign Key Indexes
All foreign keys are automatically indexed for query performance.

### Custom Composite Indexes
```sql
-- Properties
CREATE INDEX idx_properties_tenant_status ON properties(tenant_id, approval_status);
CREATE INDEX idx_properties_owner ON properties(owner_id);

-- Units
CREATE INDEX idx_units_property_status ON units(property_id, status);

-- Payments
CREATE INDEX idx_payments_lease_status ON payments(lease_id, status);
CREATE INDEX idx_payments_tenant_date ON payments(tenant_id, payment_date);

-- Leases
CREATE INDEX idx_leases_unit_status ON leases(unit_id, status);
CREATE INDEX idx_leases_tenant ON leases(tenant_id);

-- Balance Transactions
CREATE INDEX idx_balance_trans_tenant_type ON balance_transactions(tenant_id, transaction_type, created_at);
CREATE INDEX idx_balance_trans_owner ON balance_transactions(property_owner_id, transaction_type, created_at);

-- Expenses
CREATE INDEX idx_expenses_tenant_status ON expenses(tenant_id, status, expense_date);

-- Maintenance
CREATE INDEX idx_maintenance_property_status ON maintenance_requests(property_id, status);
```

---

## Constraints

### Unique Constraints
- `platform_users.email`
- `tenants.company_email`
- `users.email`
- `property_owners.email`
- `property_owners.id_number`
- `leases.lease_number`
- `subscription_invoices.invoice_number`

### Check Constraints
```sql
-- Ensure positive amounts
ALTER TABLE payments ADD CONSTRAINT chk_payment_amount_positive CHECK (amount > 0);
ALTER TABLE expenses ADD CONSTRAINT chk_expense_amount_positive CHECK (amount > 0);

-- Ensure valid percentages
ALTER TABLE properties ADD CONSTRAINT chk_fee_value_range CHECK (fee_value >= 0 AND fee_value <= 100);
ALTER TABLE tenants ADD CONSTRAINT chk_platform_fee_range CHECK (default_platform_fee_percentage >= 0 AND default_platform_fee_percentage <= 100);

-- Ensure valid dates
ALTER TABLE leases ADD CONSTRAINT chk_lease_dates CHECK (end_date > start_date);
ALTER TABLE reservations ADD CONSTRAINT chk_reservation_dates CHECK (expiry_date >= reservation_date);
```

### Cascade Rules
- `ON DELETE CASCADE`: Most foreign keys to maintain referential integrity
- `ON DELETE SET NULL`: Optional relationships (e.g., `assigned_to`, `approved_by`)

---

## Data Flow Diagram

```
┌─────────────────┐
│ Platform Owner  │
└────────┬────────┘
         │ manages
         ▼
┌─────────────────┐      creates      ┌──────────────┐
│    Tenants      │◄──────────────────│ Properties   │
│  (Companies)    │                   └──────┬───────┘
└────────┬────────┘                          │ has
         │ employs                            ▼
         ▼                            ┌──────────────┐
┌─────────────────┐                  │    Units     │
│     Users       │                  └──────┬───────┘
│ (Staff/Tenants) │                         │ rented via
└────────┬────────┘                         ▼
         │ makes                     ┌──────────────┐
         ▼                           │   Leases     │
┌─────────────────┐                  └──────┬───────┘
│   Payments      │◄────────────────────────┘
└────────┬────────┘                   generates
         │ updates
         ▼
┌─────────────────┐
│Company Balances │
└─────────────────┘
```

---

## Security Considerations

### Multi-Tenancy Isolation
- All business tables include `tenant_id` for data isolation
- Row-level security enforced at application level
- Database views can be created for additional isolation

### Sensitive Data
- Passwords: Hashed using bcrypt
- Payment details: Encrypted at rest
- Personal information: Encrypted where applicable

### Audit Trail
- All financial transactions logged in `balance_transactions`
- User actions tracked in `audit_logs`
- Payment history immutable

---

## Performance Optimization

### Query Optimization
- Composite indexes on frequently queried columns
- Covering indexes for common queries
- Partitioning strategy for large tables (future)

### Caching Strategy
- Application-level caching for static data
- Query result caching for dashboards
- Redis for session management

---

## Backup & Recovery

### Backup Strategy
- Daily full backups
- Hourly incremental backups
- Point-in-time recovery enabled
- 30-day retention policy

### Disaster Recovery
- Multi-region replication
- Automated failover
- Recovery Time Objective (RTO): 1 hour
- Recovery Point Objective (RPO): 15 minutes

---

**Generated**: January 3, 2026  
**Version**: 1.0  
**Database**: PostgreSQL 15+  
**Total Tables**: 29
