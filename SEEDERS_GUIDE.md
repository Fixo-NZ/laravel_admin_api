# 🌱 Complete Database Seeders Guide

**Purpose:** Easy database population for testing  
**Location:** `database/seeders/`  
**Command:** `php artisan db:seed`

---

## 📋 Available Seeders

All seeders are created and ready to use. They run in the correct order automatically.

### Seeder Order (Dependencies):

1. ✅ **CategorySeeder** - Creates categories (used by services)
2. ✅ **JobCategorySeeder** - Creates job categories (used by job_requests)
3. ✅ **HomeownerSeeder** - Creates homeowners with test credentials
4. ✅ **TradieSeeder** - Creates tradies (active, available, verified)
5. ✅ **ServiceSeeder** - Creates services for homeowners
6. ✅ **JobRequestSeeder** - Creates job requests
7. ✅ **TradieServiceSeeder** - Links tradies to services (pivot table)
8. ✅ **BookingSeeder** - Creates bookings

---

## 🚀 How to Use

### Option 1: Seed Everything (Recommended)

```bash
cd C:\Users\Ricardo\fixo_laravel\laravel_admin_api

# Fresh migration + seed
php artisan migrate:fresh --seed

# Or just seed (if tables already exist)
php artisan db:seed
```

### Option 2: Seed Individual Seeders

```bash
# Seed only categories
php artisan db:seed --class=CategorySeeder

# Seed only homeowners
php artisan db:seed --class=HomeownerSeeder

# Seed only bookings
php artisan db:seed --class=BookingSeeder
```

---

## 📊 What Gets Created

### Categories (10)
- Plumbing, Electrical, Carpentry, Painting, Roofing, HVAC, Flooring, Landscaping, Cleaning, Handyman

### Job Categories (8)
- Plumbing, Electrical, Carpentry, Painting, Roofing, HVAC, Flooring, Landscaping

### Homeowners (10 total)
- **5 specific test users** with known credentials
- **5 additional** from factory

**Test Credentials:**
- `john.smith@example.com` / `password123`
- `sarah.johnson@example.com` / `password123`
- `mike.williams@example.com` / `password123`
- `emma.brown@example.com` / `password123`
- `david.davis@example.com` / `password123`

### Tradies (10 total)
- **5 specific test tradies** (active, available, verified)
- **5 additional** from factory

**Test Credentials:**
- `tom.plumber@example.com` / `password123`
- `electric.master@example.com` / `password123`
- `carpenter.pro@example.com` / `password123`
- `paint.expert@example.com` / `password123`
- `roof.specialist@example.com` / `password123`

### Services (~20-30)
- 2-3 services per homeowner
- Various statuses: Pending, InProgress, Completed, Cancelled
- Linked to categories

### Job Requests (~10-20)
- 1-2 job requests per homeowner
- Various types: urgent, standard, recurring
- Linked to job categories

### Tradie-Service Links (~30-50)
- Each tradie linked to 2+ services
- Based on business name matching
- Includes base rates

### Bookings (~20-40)
- 2-4 bookings per homeowner
- Mix of past and future bookings
- Various statuses: pending, confirmed, completed, canceled
- Includes booking logs

---

## 🔍 Verify Seeding

### Check in Laravel Tinker:

```bash
php artisan tinker

# Count records
\App\Models\Category::count();
\App\Models\Homeowner::count();
\App\Models\Tradie::count();
\App\Models\Service::count();
\App\Models\Booking::count();

# Check a homeowner
\App\Models\Homeowner::first();

# Check bookings for a homeowner
\App\Models\Homeowner::first()->bookings;
```

### Check in PostgreSQL:

```sql
-- Count all tables
SELECT 
    'categories' as table_name, COUNT(*) as count FROM categories
UNION ALL
SELECT 'homeowners', COUNT(*) FROM homeowners
UNION ALL
SELECT 'tradies', COUNT(*) FROM tradies
UNION ALL
SELECT 'services', COUNT(*) FROM services
UNION ALL
SELECT 'bookings', COUNT(*) FROM bookings
UNION ALL
SELECT 'job_requests', COUNT(*) FROM job_requests
UNION ALL
SELECT 'tradie_services', COUNT(*) FROM tradie_services;

-- Check bookings with relationships
SELECT 
    b.id,
    b.booking_number,
    b.status,
    h.email as homeowner,
    t.business_name as tradie,
    s.job_description as service,
    b.booking_start
FROM bookings b
JOIN homeowners h ON b.homeowner_id = h.id
JOIN tradies t ON b.tradie_id = t.id
JOIN services s ON b.service_id = s.id
LIMIT 10;
```

---

## 🐛 Troubleshooting

### Error: "No homeowners found"
**Solution:** Run seeders in order:
```bash
php artisan db:seed --class=HomeownerSeeder
php artisan db:seed --class=ServiceSeeder
```

### Error: "No categories found"
**Solution:** Run CategorySeeder first:
```bash
php artisan db:seed --class=CategorySeeder
```

### Error: Foreign key constraint fails
**Solution:** Run `migrate:fresh` to reset database:
```bash
php artisan migrate:fresh --seed
```

### Error: "Tradie not available"
**Solution:** Ensure TradieSeeder sets proper status:
- `availability_status` = 'available'
- `status` = 'active'
- `verified_at` = not null

---

## ✅ Success Indicators

After seeding, you should see:

```
✅ Created 10 categories
✅ Created 8 job categories
✅ Created 10 homeowners
📧 Test login: john.smith@example.com / password123
✅ Created 10 tradies
📧 Test tradie login: tom.plumber@example.com / password123
✅ Created 25 services for homeowners
✅ Created 15 job requests
✅ Created 35 tradie-service relationships
✅ Created 30 bookings
✅ Database seeding completed successfully!
```

---

## 🎯 Quick Test

1. **Seed database:**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Login in Flutter app:**
   - Email: `john.smith@example.com`
   - Password: `password123`

3. **Check bookings:**
   - Navigate to "My Bookings"
   - Should see multiple bookings

4. **Check services:**
   - Navigate to "My Jobs"
   - Should see service requests

5. **Find tradies:**
   - Create a service
   - Get recommendations
   - Should see available tradies

---

## 📝 Notes

- All passwords are: `password123`
- Tradies are set to `active`, `available`, and `verified`
- Bookings include past and future dates
- Services are linked to homeowners and categories
- Tradies are linked to services via pivot table

---

**Status:** ✅ **All Seeders Ready - Just Run `php artisan db:seed`!**

