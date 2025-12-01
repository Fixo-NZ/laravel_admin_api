# Testing Booking Flow - Database Setup Guide

## Overview
To test the "Find Your Tradie" recommendations and booking flow, you need:

1. **A Homeowner** (to create services)
2. **Job Categories** (Electrical, Plumbing, etc.)
3. **A Service** (created by homeowner)
4. **A JobRequest** (with location data - for recommendations to work)
5. **Tradies** (active, available, verified, with location & services attached)

---

## Step 1: Create Job Categories (if not exists)

Run this SQL in your PostgreSQL database:

```sql
INSERT INTO job_categories (category_name, description, is_active, created_at, updated_at)
VALUES 
    ('Electrical', 'Electrical services and repairs', true, NOW(), NOW()),
    ('Plumbing', 'Plumbing installation and repairs', true, NOW(), NOW()),
    ('Painting', 'Interior and exterior painting', true, NOW(), NOW()),
    ('Building', 'Construction and building services', true, NOW(), NOW())
ON CONFLICT DO NOTHING;
```

Or use Laravel Tinker:
```bash
php artisan tinker
```

```php
\App\Models\JobCategories::firstOrCreate(
    ['category_name' => 'Electrical'],
    ['description' => 'Electrical services', 'is_active' => true]
);
\App\Models\JobCategories::firstOrCreate(
    ['category_name' => 'Plumbing'],
    ['description' => 'Plumbing services', 'is_active' => true]
);
```

---

## Step 2: Create/Get a Homeowner

If you don't have one, create via seeder or SQL:

```sql
-- Get existing homeowner or create one
INSERT INTO homeowners (first_name, last_name, email, phone, password, status, created_at, updated_at)
VALUES 
    ('Test', 'Homeowner', 'homeowner@test.com', '0211234567', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NOW(), NOW())
ON CONFLICT (email) DO NOTHING
RETURNING id;
```

Password is: `password` (hashed)

---

## Step 3: Create a Service (for urgent booking)

```sql
-- Get homeowner_id and job_category_id first
-- Replace 1 with actual homeowner_id and job_category_id

INSERT INTO services (homeowner_id, job_categoryid, job_description, location, status, created_at, updated_at)
VALUES 
    (
        1,  -- homeowner_id (replace with actual)
        1,  -- job_categoryid (replace with Electrical category id)
        'Need urgent electrical repair - lights not working',
        '123 Main St, Auckland',
        'Pending',
        NOW(),
        NOW()
    )
RETURNING id;
```

**Note the service `id`** - you'll use this in Flutter app!

---

## Step 4: Create a JobRequest (REQUIRED for recommendations to work)

The `TradieRecommendationController` needs a `JobRequest` with location data:

```sql
-- Get the service's job_categoryid
-- Replace values with actual IDs

INSERT INTO job_requests (
    homeowner_id,
    job_category_id,
    title,
    description,
    job_type,
    status,
    budget,
    location,
    latitude,
    longitude,
    created_at,
    updated_at
)
VALUES 
    (
        1,  -- homeowner_id (same as service)
        1,  -- job_category_id (same as service's job_categoryid)
        'Urgent Electrical Repair',
        'Need urgent electrical repair - lights not working',
        'urgent',
        'pending',
        150.00,  -- budget in dollars
        '123 Main St, Auckland',
        -36.8485,  -- Auckland latitude
        174.7633,  -- Auckland longitude
        NOW(),
        NOW()
    )
RETURNING id;
```

**IMPORTANT**: The `JobRequest` ID should match or be linked to your Service somehow, OR you need to update the route bridge to map Service -> JobRequest correctly.

---

## Step 5: Create Tradies (with all required fields)

Tradies must have:
- `status = 'active'`
- `availability_status = 'available'`
- `verified_at IS NOT NULL` (verified)
- `latitude` and `longitude` (within service radius of JobRequest)
- `service_radius` (e.g., 50 km)
- Connected to services via `tradie_services` pivot table

```sql
-- Create a tradie in Auckland (near the JobRequest location)
INSERT INTO tradies (
    first_name,
    last_name,
    middle_name,
    email,
    phone,
    password,
    city,
    region,
    latitude,
    longitude,
    business_name,
    years_experience,
    hourly_rate,
    availability_status,
    service_radius,
    verified_at,
    status,
    created_at,
    updated_at
)
VALUES 
    (
        'John',
        'Electrician',
        'M',
        'john.electrician@test.com',
        '0219876543',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: password
        'Auckland',
        'Auckland',
        -36.8500,  -- Auckland latitude (close to job location)
        174.7650,  -- Auckland longitude (close to job location)
        'John''s Electrical Services',
        10,
        80.00,  -- hourly rate
        'available',
        50,  -- service radius in km
        NOW(),  -- verified_at (IMPORTANT!)
        'active',
        NOW(),
        NOW()
    )
RETURNING id;
```

**Note the tradie `id`**!

---

## Step 6: Link Tradie to Service (via pivot table)

```sql
-- Link tradie to the service category
-- You need to find a Service record in the 'services' table that matches your category
-- OR link via job_category

-- First, get service IDs that match your category
-- Then link tradie to those services

INSERT INTO tradie_services (service_id, tradie_id, created_at, updated_at)
SELECT 
    s.id as service_id,
    1 as tradie_id,  -- Replace with actual tradie_id from Step 5
    NOW(),
    NOW()
FROM services s
WHERE s.job_categoryid = 1  -- Replace with your job_category_id
LIMIT 1;
```

---

## Step 7: Update the Route Bridge (if needed)

In `routes/api.php`, make sure the bridge route correctly maps Service -> JobRequest:

```php
Route::middleware('auth:sanctum')->get('/services/{serviceId}/recommend-tradies', function ($serviceId) {
    $service = \App\Models\Service::findOrFail($serviceId);
    
    // Find or create a JobRequest for this service
    $jobRequest = \App\Models\JobRequest::where('homeowner_id', $service->homeowner_id)
        ->where('job_category_id', $service->job_categoryid)
        ->where('status', '!=', 'cancelled')
        ->first();
    
    if (!$jobRequest) {
        return response()->json([
            'success' => true,
            'message' => 'No job request found for this service.',
            'data' => [],
        ], 200);
    }
    
    return app(\App\Http\Controllers\TradieRecommendationController::class)
        ->recommend($jobRequest->id);
});
```

---

## Quick Test Checklist

After populating:

1. ✅ **Homeowner exists** (can login in Flutter app)
2. ✅ **Service exists** (shows in urgent booking list)
3. ✅ **JobRequest exists** with latitude/longitude matching tradie location
4. ✅ **Tradie exists** with:
   - `status = 'active'`
   - `availability_status = 'available'`
   - `verified_at IS NOT NULL`
   - `latitude/longitude` within `service_radius` of JobRequest
   - Linked to service via `tradie_services`
5. ✅ **Route bridge** works (`/services/{id}/recommend-tradies`)

---

## Testing in Flutter App

1. **Login** as the homeowner
2. **Go to Urgent Booking** screen
3. **Select the service** you created
4. **View Recommendations** - should show the tradie you created
5. **Click "Book Now"** on a tradie
6. **Fill the booking flow** (Service, Schedule, Contact, Review)
7. **Submit** - should create urgent booking in database
8. **Check database** - `urgent_bookings` table should have new row

---

## Troubleshooting

**No tradies showing?**
- Check tradie `status = 'active'`
- Check `availability_status = 'available'`
- Check `verified_at IS NOT NULL`
- Check tradie `latitude/longitude` is within `service_radius` of JobRequest location
- Check `tradie_services` pivot table has the link

**Route not found?**
- Make sure route bridge is added in `routes/api.php`
- Run `php artisan route:clear`

**Booking not creating?**
- Check `urgent_bookings` table exists (run migrations)
- Check homeowner is authenticated (Bearer token)
- Check `job_id` matches a valid service/job

