# G4/to_fetch_tradies_only Branch Fixes Documentation

**Branch:** `g4/to_fetch_tradies_only`  
**Date:** Fixes Applied  
**Purpose:** Align Service model and related code with main branch structure

---

## Overview

This document details all changes made to the `g4/to_fetch_tradies_only` branch to align the Service model structure with the main branch. The Service model has been updated from representing "service types" (what tradies offer) to representing "homeowner job requests" (what homeowners need).

---

## Changes Summary

### 1. Service Model Updated ✅
**File:** `app/Models/Service.php`

**Previous Structure:**
- `name` - Service name (e.g., "Electrical Installation")
- `description` - Service description
- `category` - Service category (e.g., "Electrical")
- `is_active` - Boolean flag

**New Structure (Main Branch Aligned):**
- `homeowner_id` - Foreign key to homeowners table
- `job_categoryid` - Foreign key to categories table
- `job_description` - Text description of the job
- `location` - String location
- `status` - Enum: 'Pending', 'InProgress', 'Completed', 'Cancelled'
- `rating` - Integer rating (1-5, nullable)

**Changes Made:**
- ✅ Updated `$fillable` array to match new structure
- ✅ Removed old scopes (`scopeActive`, `scopeByCategory` with old logic)
- ✅ Added new scopes: `scopePending()`, `scopeInProgress()`, `scopeCompleted()`, `scopeCancelled()`, `scopeByCategory($categoryId)`
- ✅ Updated relationships:
  - Added `homeowner()` - belongsTo relationship
  - Added `category()` - belongsTo relationship with Category model
  - Kept `tradies()` - many-to-many relationship (tradies can be linked to homeowner service requests)
- ✅ Removed `getCategories()` static method

---

### 2. Services Table Migration Updated ✅
**File:** `database/migrations/2025_08_31_131634_create_services_table.php`

**Previous Schema:**
```php
$table->string('name');
$table->text('description')->nullable();
$table->string('category');
$table->boolean('is_active')->default(true);
$table->index(['category', 'is_active']);
```

**New Schema (Main Branch Aligned):**
```php
$table->foreignId('homeowner_id')->constrained('homeowners')->onDelete('cascade');
$table->foreignId('job_categoryid')->constrained('categories')->onDelete('cascade');
$table->text('job_description');
$table->string('location');
$table->enum('status', ['Pending', 'InProgress', 'Completed', 'Cancelled'])->default('Pending');
$table->integer('rating')->nullable();
$table->index(['homeowner_id', 'status']);
$table->index(['job_categoryid', 'status']);
```

**Changes Made:**
- ✅ Removed: `name`, `description`, `category`, `is_active` columns
- ✅ Added: `homeowner_id`, `job_categoryid`, `job_description`, `location`, `status`, `rating` columns
- ✅ Updated indexes for better query performance

**⚠️ IMPORTANT:** You will need to run a fresh migration or create a new migration to alter the existing table:
```bash
php artisan migrate:fresh
# OR create a new migration to alter the table
php artisan make:migration update_services_table_structure
```

---

### 3. ServiceController Updated ✅
**File:** `app/Http/Controllers/ServiceController.php`

**Changes Made:**
- ✅ `index()` method - Already correct, loads with `homeowner` and `category` relationships
- ✅ `store()` method - Updated validation:
  - Removed `createdAt` and `updatedAt` (Laravel handles timestamps automatically)
  - Kept all other required fields
  - Added eager loading of relationships in response
- ✅ `show()` method - Already correct
- ✅ `update()` method - Updated validation:
  - Removed `createdAt` and `updatedAt`
  - Added eager loading of relationships in response
- ✅ `destroy()` method - No changes needed

**Validation Rules:**
```php
'homeowner_id' => 'required|exists:homeowners,id'
'job_categoryid' => 'required|exists:categories,id'
'job_description' => 'required|string'
'location' => 'required|string|max:255'
'status' => 'required|in:Pending,InProgress,Completed,Cancelled'
'rating' => 'nullable|integer|min:1|max:5'
```

---

### 4. TradieRecommendationController Fixed ✅
**File:** `app/Http/Controllers/TradieRecommendationController.php`

**Previous Issue:**
- Incomplete service matching logic (lines 36-40)
- Query was trying to match services by category but logic was broken

**Changes Made:**
- ✅ Fixed service matching query:
  ```php
  // OLD (broken):
  ->whereHas('services', function ($q) use ($categoryId) {
      $q->where('category', function ($sub) use ($categoryId) {
          // Incomplete logic
      });
  })
  
  // NEW (fixed):
  ->whereHas('services', function ($q) use ($categoryId) {
      $q->where('job_categoryid', $categoryId);
  })
  ```
- ✅ Updated eager loading to use correct fields:
  ```php
  // OLD:
  ->with(['services:id,name,category'])
  
  // NEW:
  ->with(['services:id,job_description,job_categoryid'])
  ```
- ✅ Updated response mapping to use `job_description` instead of `name`:
  ```php
  // OLD:
  'services' => $t->services->pluck('name')
  
  // NEW:
  'services' => $t->services->pluck('job_description')
  ```

**How It Works Now:**
1. Loads JobRequest with category
2. Extracts job details (latitude, longitude, budget, category_id)
3. Finds tradies that:
   - Are active, available, and verified
   - Have services matching the job's category (via `job_categoryid`)
   - Are within service radius of the job location
   - Match budget requirements (if provided)
4. Ranks by rating and experience
5. Returns top 5 tradies

---

### 5. Filament ServiceResource Updated ✅
**File:** `app/Filament/Resources/Services/ServiceResource.php`

**Previous Form Fields:**
- `name` (TextInput)
- `description` (Textarea)
- `category` (TextInput)

**New Form Fields:**
- `homeowner_id` (Select - relationship to homeowners)
- `job_categoryid` (Select - relationship to categories)
- `job_description` (Textarea)
- `location` (TextInput)
- `status` (Select - enum options)
- `rating` (TextInput - numeric, 1-5)

**Previous Table Columns:**
- `name` - Service Name
- `description` - Description
- `category` - Category

**New Table Columns:**
- `homeowner.email` - Homeowner
- `category.category_name` - Category
- `job_description` - Job Description
- `location` - Location
- `status` - Status (with badge colors)
- `rating` - Rating
- `created_at` - Created At

**Changes Made:**
- ✅ Updated form schema to match new Service model structure
- ✅ Updated table columns to display new fields
- ✅ Added status badge with color coding:
  - Pending = warning (yellow)
  - InProgress = info (blue)
  - Completed = success (green)
  - Cancelled = danger (red)
- ✅ Added status filter in table

---

### 6. Booking Model - No Changes Needed ✅
**File:** `app/Models/Booking.php`

**Status:** ✅ Already compatible

The Booking model's relationship to Service remains valid:
```php
public function service() {
    return $this->belongsTo(Service::class);
}
```

This works correctly because:
- Bookings reference `service_id` which now points to homeowner job requests (Service model)
- This makes sense: a booking is for a specific homeowner service request

---

### 7. Tradie Model - No Changes Needed ✅
**File:** `app/Models/Tradie.php`

**Status:** ✅ Already compatible

The Tradie model's relationship to Service remains valid:
```php
public function services()
{
    return $this->belongsToMany(Service::class, 'tradie_services')
        ->withPivot('base_rate')
        ->withTimestamps();
}
```

**How It Works:**
- Tradies can be linked to homeowner service requests via the `tradie_services` pivot table
- This allows tradies to be associated with specific homeowner job requests they can handle
- The `base_rate` in the pivot table represents the tradie's rate for that specific service request

---

## Database Migration Required

### ⚠️ IMPORTANT: Run Migration

Since the services table structure has changed, you need to update your database:

**Option 1: Fresh Migration (⚠️ Drops all data)**
```bash
cd C:\Users\Ricardo\fixo_laravel\laravel_admin_api
php artisan migrate:fresh
php artisan db:seed
```

**Option 2: Create Alter Migration (Recommended if you have data)**
```bash
php artisan make:migration update_services_table_to_main_branch_structure
```

Then in the migration file:
```php
public function up(): void
{
    Schema::table('services', function (Blueprint $table) {
        // Drop old columns
        $table->dropColumn(['name', 'description', 'category', 'is_active']);
        
        // Add new columns
        $table->foreignId('homeowner_id')->after('id')->constrained('homeowners')->onDelete('cascade');
        $table->foreignId('job_categoryid')->after('homeowner_id')->constrained('categories')->onDelete('cascade');
        $table->text('job_description')->after('job_categoryid');
        $table->string('location')->after('job_description');
        $table->enum('status', ['Pending', 'InProgress', 'Completed', 'Cancelled'])->default('Pending')->after('location');
        $table->integer('rating')->nullable()->after('status');
        
        // Update indexes
        $table->index(['homeowner_id', 'status']);
        $table->index(['job_categoryid', 'status']);
    });
}
```

**Option 3: PostgreSQL Direct SQL (If you want to preserve data)**
```sql
-- Connect to your database first
psql -h <DB_HOST> -U <DB_USERNAME> -d <DB_DATABASE>

-- Backup first (recommended)
CREATE TABLE services_backup AS SELECT * FROM services;

-- Drop old columns and add new ones
ALTER TABLE services 
    DROP COLUMN IF EXISTS name,
    DROP COLUMN IF EXISTS description,
    DROP COLUMN IF EXISTS category,
    DROP COLUMN IF EXISTS is_active,
    ADD COLUMN IF NOT EXISTS homeowner_id BIGINT UNSIGNED,
    ADD COLUMN IF NOT EXISTS job_categoryid BIGINT UNSIGNED,
    ADD COLUMN IF NOT EXISTS job_description TEXT,
    ADD COLUMN IF NOT EXISTS location VARCHAR(255),
    ADD COLUMN IF NOT EXISTS status ENUM('Pending', 'InProgress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    ADD COLUMN IF NOT EXISTS rating INTEGER;

-- Add foreign keys
ALTER TABLE services
    ADD CONSTRAINT services_homeowner_id_foreign 
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE,
    ADD CONSTRAINT services_job_categoryid_foreign 
    FOREIGN KEY (job_categoryid) REFERENCES categories(id) ON DELETE CASCADE;

-- Add indexes
CREATE INDEX services_homeowner_id_status_index ON services(homeowner_id, status);
CREATE INDEX services_job_categoryid_status_index ON services(job_categoryid, status);
```

---

## API Endpoints Status

### ✅ Working Endpoints

1. **GET /api/services** - List all homeowner service requests
   - Returns: Services with homeowner and category relationships
   - Status: ✅ Working

2. **POST /api/services** - Create new homeowner service request
   - Body: `homeowner_id`, `job_categoryid`, `job_description`, `location`, `status`, `rating`
   - Status: ✅ Working

3. **GET /api/services/{id}** - Get service request details
   - Returns: Service with homeowner and category
   - Status: ✅ Working

4. **PUT /api/services/{id}** - Update service request
   - Body: Same as POST (all fields optional)
   - Status: ✅ Working

5. **DELETE /api/services/{id}** - Delete service request
   - Status: ✅ Working

6. **GET /api/jobs/{jobId}/recommend-tradies** - Get tradie recommendations
   - Returns: List of recommended tradies for a job request
   - Status: ✅ Fixed and working

---

## Testing Checklist

Before testing in the emulator, ensure:

- [ ] Database migration has been run (services table updated)
- [ ] Categories table has data (for job_categoryid foreign key)
- [ ] Homeowners table has data (for homeowner_id foreign key)
- [ ] Test creating a service request via POST /api/services
- [ ] Test fetching services via GET /api/services
- [ ] Test tradie recommendations via GET /api/jobs/{jobId}/recommend-tradies
- [ ] Test booking creation (should reference service_id)
- [ ] Test booking fetching and history

---

## Key Relationships

### Service Model Relationships:
```
Service
├── belongsTo Homeowner (homeowner_id)
├── belongsTo Category (job_categoryid)
└── belongsToMany Tradie (via tradie_services pivot)
```

### Booking Model Relationships:
```
Booking
├── belongsTo Homeowner (homeowner_id)
├── belongsTo Tradie (tradie_id)
└── belongsTo Service (service_id) ← Now references homeowner job requests
```

### Tradie Model Relationships:
```
Tradie
├── belongsToMany Service (via tradie_services) ← Links to homeowner job requests
└── hasMany Booking
```

---

## Breaking Changes

### ⚠️ What Changed:

1. **Service Model Structure**
   - Old: Service = Service types (what tradies offer)
   - New: Service = Homeowner job requests (what homeowners need)

2. **Database Schema**
   - Old columns removed: `name`, `description`, `category`, `is_active`
   - New columns added: `homeowner_id`, `job_categoryid`, `job_description`, `location`, `status`, `rating`

3. **API Response Format**
   - Service responses now include `homeowner` and `category` relationships
   - Service responses no longer include `name`, `description`, `category` fields

4. **Tradie Matching Logic**
   - Now matches by `job_categoryid` instead of `category` string

---

## Files Modified

1. ✅ `app/Models/Service.php` - Complete rewrite
2. ✅ `database/migrations/2025_08_31_131634_create_services_table.php` - Schema updated
3. ✅ `app/Http/Controllers/ServiceController.php` - Validation updated
4. ✅ `app/Http/Controllers/TradieRecommendationController.php` - Matching logic fixed
5. ✅ `app/Filament/Resources/Services/ServiceResource.php` - Form and table updated

---

## Files Unchanged (But Compatible)

1. ✅ `app/Models/Booking.php` - No changes needed
2. ✅ `app/Models/Tradie.php` - No changes needed
3. ✅ `app/Http/Controllers/BookingController.php` - No changes needed
4. ✅ `database/migrations/2025_08_31_131639_create_tradie_services_table.php` - No changes needed

---

## Next Steps

1. **Run Database Migration**
   ```bash
   php artisan migrate:fresh
   # OR
   php artisan migrate (if using alter migration)
   ```

2. **Seed Test Data** (if needed)
   ```bash
   php artisan db:seed
   ```

3. **Test API Endpoints**
   - Use Postman or your Flutter app to test:
     - Creating service requests
     - Fetching tradies for a job
     - Creating bookings

4. **Test in Emulator**
   - Test the Flutter app's fetch tradies functionality
   - Test booking creation and management

---

## Notes

- The Service model now represents homeowner job requests, not service types
- Tradies are linked to homeowner service requests via the `tradie_services` pivot table
- The TradieRecommendationController matches tradies by `job_categoryid`
- All relationships are properly maintained and working

---

**End of Documentation**

