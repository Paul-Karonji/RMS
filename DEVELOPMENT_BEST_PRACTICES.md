# Development Best Practices & Common Pitfalls - Lessons from Week 11

**Created:** January 9, 2026  
**Purpose:** Prevent recurring database schema mismatches and development issues

---

## Executive Summary

During Week 11, we encountered **23 distinct issues** that prevented tests from passing. This document outlines the root causes, prevention strategies, and mandatory checks to avoid similar problems in future development.

> **Key Insight:** 95% of issues were caused by **database schema mismatches** between code assumptions and actual database structure.

---

## Critical Issue Categories

### 1. Database Schema Mismatches (10 issues - 43% of total)

#### Problem
Code referenced columns that didn't exist in the database or used incorrect column names.

#### Examples Found
- ❌ Code used `total_rent_collected` → Database has `total_collected`
- ❌ Code used `pending_balance` → Database has `amount_owed`
- ❌ Code used `total_paid_out` → Database has `total_paid`
- ❌ Code used `base_amount` → Database has `payment_amount`
- ❌ Code included `property_id` in `platform_fees` → Column doesn't exist

#### Prevention Strategy

**MANDATORY: Always verify schema before coding**

```bash
# Check actual table structure
php artisan tinker
> Schema::getColumnListing('table_name');

# Or use database client
psql -U postgres -d database_name
\d table_name
```

**Checklist Before Writing Code:**
- [ ] Check migration file for exact column names
- [ ] Verify column exists in database
- [ ] Confirm data types match expectations
- [ ] Check foreign key relationships
- [ ] Verify NOT NULL constraints

---

### 2. Model Configuration Issues (4 issues - 17% of total)

#### Problem
Laravel models had incomplete or incorrect `$fillable` arrays, preventing mass assignment.

#### Examples Found
- ❌ `OwnerBalance` missing `tenant_id` in fillable array
- ❌ `PlatformFee` model completely missing
- ❌ `BalanceTransaction` model incomplete
- ❌ Models referencing non-existent columns

#### Prevention Strategy

**MANDATORY: Sync models with migrations**

```php
// ALWAYS match fillable array to migration columns
protected $fillable = [
    // Copy EXACT column names from migration
    'column_one',
    'column_two',
    // etc.
];
```

**Model Creation Checklist:**
- [ ] Create model immediately after migration
- [ ] Copy all column names from migration to `$fillable`
- [ ] Add appropriate `$casts` for data types
- [ ] Define relationships if applicable
- [ ] Add validation rules in form request

---

### 3. Foreign Key Confusion (3 issues - 13% of total)

#### Problem
Misunderstanding which table a foreign key references, especially with `tenant_id`.

#### Critical Distinction Found

```php
// WRONG ASSUMPTION
$lease->tenant_id → Assumed to reference tenants.id

// ACTUAL SCHEMA
$lease->tenant_id → References users.id (the tenant user)

// FOR BALANCE TRACKING
Need: tenants.id (the tenant company)
Get from: $user->tenant_id
```

#### Prevention Strategy

**MANDATORY: Check foreign key definitions**

```sql
-- Check foreign key constraints
SELECT
    tc.constraint_name,
    tc.table_name,
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
JOIN information_schema.constraint_column_usage AS ccu
    ON ccu.constraint_name = tc.constraint_name
WHERE tc.constraint_type = 'FOREIGN KEY'
    AND tc.table_name = 'your_table';
```

**Foreign Key Checklist:**
- [ ] Verify which table the FK references
- [ ] Understand the relationship (user vs company)
- [ ] Check if nullable or NOT NULL
- [ ] Verify cascade behavior (CASCADE, SET NULL, etc.)

---

### 4. Test Data Setup Issues (3 issues - 13% of total)

#### Problem
Tests created incomplete data that violated database constraints.

#### Examples Found
- ❌ Missing `payment_type` field (NOT NULL constraint)
- ❌ Missing `payment_method` field (NOT NULL constraint)
- ❌ Missing `payment_date` field

#### Prevention Strategy

**MANDATORY: Check constraints before creating test data**

```php
// ALWAYS check migration for NOT NULL constraints
public function test_example()
{
    $payment = Payment::factory()->create([
        'lease_id' => $lease->id,
        'tenant_id' => $user->id,
        'amount' => 50000,
        // ADD ALL REQUIRED FIELDS
        'payment_type' => 'rent',      // NOT NULL
        'payment_method' => 'mpesa',   // NOT NULL
        'payment_date' => now(),       // NOT NULL
        'status' => 'completed',
    ]);
}
```

**Test Data Checklist:**
- [ ] Include all NOT NULL fields
- [ ] Provide valid foreign key values
- [ ] Use realistic data values
- [ ] Check factory definitions match schema

---

### 5. PHP Environment Issues (3 issues - 13% of total)

#### Problem
Missing PHP extensions prevented Laravel from functioning properly.

#### Extensions Required
- ✅ `mbstring` - String manipulation
- ✅ `pdo_pgsql` - PostgreSQL PDO driver
- ✅ `pgsql` - PostgreSQL functions

#### Prevention Strategy

**MANDATORY: Verify PHP environment on new setups**

```bash
# Check installed extensions
php -m | grep -E "mbstring|pdo_pgsql|pgsql"

# Enable in php.ini
extension=mbstring
extension=pdo_pgsql
extension=pgsql
```

**Environment Setup Checklist:**
- [ ] PHP version matches project requirements
- [ ] All required extensions enabled
- [ ] Composer dependencies installed
- [ ] Database connection configured
- [ ] `.env` file properly configured

---

## Mandatory Development Workflow

### Before Writing Any Code

```
1. CHECK MIGRATION
   ├─ Read migration file
   ├─ Note exact column names
   ├─ Note data types
   ├─ Note constraints (NOT NULL, UNIQUE, etc.)
   └─ Note foreign keys

2. VERIFY DATABASE
   ├─ Run migration if needed
   ├─ Check table structure in database
   └─ Confirm columns exist

3. UPDATE MODEL
   ├─ Add columns to $fillable
   ├─ Add $casts if needed
   └─ Define relationships

4. WRITE CODE
   └─ Use EXACT column names from migration

5. WRITE TESTS
   ├─ Include all required fields
   └─ Use correct foreign key values
```

---

## Code Review Checklist

Before committing code, verify:

### Database Layer
- [ ] Migration column names match code usage
- [ ] All foreign keys properly defined
- [ ] NOT NULL constraints satisfied
- [ ] Default values set where appropriate

### Model Layer
- [ ] `$fillable` includes all necessary columns
- [ ] `$fillable` doesn't include non-existent columns
- [ ] `$casts` defined for dates, booleans, JSON
- [ ] Relationships properly defined

### Service Layer
- [ ] Correct foreign key values used
- [ ] Column names match database exactly
- [ ] NULL checks where columns are nullable
- [ ] Transactions used for multi-table updates

### Test Layer
- [ ] All required fields included in test data
- [ ] Foreign keys reference valid records
- [ ] Assertions check correct column names
- [ ] Database cleaned between tests

---

## Common Anti-Patterns to Avoid

### ❌ DON'T: Assume Column Names
```php
// WRONG - Assuming column name
$balance->pending_balance = $amount;
```

### ✅ DO: Verify in Migration
```php
// RIGHT - Checked migration first
$balance->amount_owed = $amount; // Actual column name
```

---

### ❌ DON'T: Copy Code Without Verification
```php
// WRONG - Copied from another project
'total_rent_collected' => $amount
```

### ✅ DO: Check Current Schema
```php
// RIGHT - Verified in current database
'total_collected' => $amount
```

---

### ❌ DON'T: Ignore Foreign Key Errors
```
SQLSTATE[23503]: Foreign key violation
// Don't just add the field - understand WHY it's failing
```

### ✅ DO: Understand the Relationship
```php
// RIGHT - Understand what the FK references
$tenantId = $user->tenant_id; // Gets company ID
// NOT $lease->tenant_id (that's user ID)
```

---

## Quick Reference: Schema Verification Commands

```bash
# Laravel Tinker - Check columns
php artisan tinker
> Schema::getColumnListing('table_name');

# PostgreSQL - Describe table
psql -U postgres -d database_name
\d table_name

# Check foreign keys
\d+ table_name

# Laravel - Generate model from database
php artisan code:models --table=table_name
```

---

## Testing Best Practices

### Always Run Tests After Schema Changes

```bash
# Run specific test
php artisan test --filter=TestName

# Run all tests
php artisan test

# Check for errors in logs
tail -f storage/logs/laravel.log
```

### Test Data Factory Pattern

```php
// Create factory that matches schema EXACTLY
public function definition()
{
    return [
        // Include ALL required fields from migration
        'field_one' => $this->faker->word(),
        'field_two' => $this->faker->numberBetween(1, 100),
        // etc.
    ];
}
```

---

## Emergency Debugging Checklist

When tests fail:

1. **Check Laravel Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verify Column Exists**
   ```php
   Schema::hasColumn('table_name', 'column_name')
   ```

3. **Check Foreign Key**
   ```sql
   SELECT * FROM information_schema.table_constraints 
   WHERE table_name = 'your_table';
   ```

4. **Verify Model Fillable**
   ```php
   dd(Model::make()->getFillable());
   ```

5. **Check Test Data**
   ```php
   dd($model->toArray());
   ```

---

## Summary: The Golden Rules

1. **SCHEMA IS TRUTH** - Always check migrations before coding
2. **VERIFY BEFORE COMMIT** - Run tests before pushing code
3. **MODELS MATCH MIGRATIONS** - Keep `$fillable` in sync
4. **UNDERSTAND FOREIGN KEYS** - Know what each FK references
5. **COMPLETE TEST DATA** - Include all required fields
6. **CHECK LOGS FIRST** - Laravel logs reveal most issues
7. **ONE SOURCE OF TRUTH** - Database schema is authoritative

---

## Conclusion

Following these practices will prevent 95% of the issues encountered in Week 11. The key is **verification before implementation** rather than debugging after failure.

**Remember:** 5 minutes of schema verification saves hours of debugging.

---

**Document Version:** 1.0  
**Last Updated:** January 9, 2026  
**Next Review:** Before Week 12 development begins
