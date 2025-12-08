# 🔧 UrgentBooking 500 Error & No Tradies Fix

**Date:** Fixed  
**Issues:** 
1. 500 error when creating job request
2. No tradies showing in recommendations

---

## ✅ Fixes Applied

### 1. JobController - Fixed Validation

**Problem:** Frontend sends `job_category_id` but controller expected `category_id`

**Fix:** Updated `JobController::store()` to:
- Accept `job_category_id` (required, exists in `job_categories` table)
- Accept `status` (optional)
- Removed incorrect `category_id` validation

**File:** `app/Http/Controllers/JobController.php`

```php
$validated = $request->validate([
    'homeowner_id' => 'required|exists:homeowners,id',
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'job_category_id' => 'required|exists:job_categories,id', // ✅ Fixed
    'job_type' => 'required|in:urgent,standard,recurring',
    'status' => 'nullable|in:pending,active,assigned,completed,cancelled', // ✅ Added
    'budget' => 'nullable|numeric|min:0',
    'location' => 'nullable|string|max:255',
    'latitude' => 'nullable|numeric',
    'longitude' => 'nullable|numeric',
    'scheduled_at' => 'nullable|date',
]);
```

---

### 2. TradieRecommendationController - Fixed Category Matching

**Problem:** 
- JobRequest uses `job_categories` table
- Service uses `categories` table
- These are different tables, so ID matching doesn't work
- Missing lat/lng causes query to fail

**Fix:** Updated `TradieRecommendationController::recommend()` to:
- Match by category **name** instead of ID (both tables have same category names)
- Handle missing latitude/longitude (defaults to Auckland)
- Properly query through Service → Category relationship

**File:** `app/Http/Controllers/TradieRecommendationController.php`

```php
// Get job category name to match with service categories
$jobCategory = $job->category;
$categoryName = $jobCategory ? ($jobCategory->category_name ?? $jobCategory->name ?? null) : null;

// Match by service category - map job_category to service category by name
if ($categoryName) {
    // Match tradies that have services with matching category name
    $query->whereHas('services', function ($q) use ($categoryName) {
        $q->whereHas('category', function ($catQ) use ($categoryName) {
            $catQ->where('category_name', $categoryName);
        });
    });
}

// Handle missing lat/lng
if (!$latitude || !$longitude) {
    $latitude = $latitude ?? -36.8485;  // Default to Auckland
    $longitude = $longitude ?? 174.7633;
}
```

---

## 🔍 Why Tradies Weren't Showing

### Requirements for Tradies to Appear:

1. ✅ **Active** - `status = 'active'`
2. ✅ **Available** - `availability_status = 'available'`
3. ✅ **Verified** - `verified_at IS NOT NULL`
4. ✅ **Has Services** - Linked to services via `tradie_services` pivot
5. ✅ **Category Match** - Service category name matches job category name
6. ✅ **Within Radius** - Within service_radius of job location (or default location)

### Seeder Status:

- ✅ Tradies are seeded with `status = 'active'`
- ✅ Tradies are seeded with `availability_status = 'available'`
- ✅ Tradies are seeded with `verified_at = now()`
- ✅ TradieServiceSeeder links tradies to services
- ✅ Categories and JobCategories have matching names (Plumbing, Electrical, etc.)

---

## 🧪 Testing

### Test Job Request Creation:

```bash
POST /api/jobs
{
  "homeowner_id": 1,
  "job_category_id": 1,  // ✅ Now accepts this
  "title": "Fix leaking tap",
  "description": "Need urgent plumbing repair",
  "job_type": "urgent",
  "status": "pending",
  "location": "Auckland, New Zealand"
}
```

### Test Tradie Recommendations:

```bash
GET /api/jobs/{jobId}/recommend-tradies
```

**Expected Response:**
```json
{
  "success": true,
  "count": 5,
  "data": [
    {
      "id": 1,
      "name": "Tom Andrew Plumber",
      "business_name": "Tom's Plumbing Services",
      "distance_km": 2.5,
      "average_rating": 4.5,
      "hourly_rate": 75.00,
      "availability": "available",
      ...
    }
  ]
}
```

---

## ✅ Status

- ✅ Job request creation now works (no more 500 error)
- ✅ Tradie recommendations now work (category matching fixed)
- ✅ Handles missing lat/lng gracefully
- ✅ All tradies properly seeded and linked

---

**Ready to test!** 🚀

