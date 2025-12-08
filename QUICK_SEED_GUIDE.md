# 🚀 Quick Seed Guide - One Command to Populate Everything!

**Location:** `C:\Users\Ricardo\fixo_laravel\laravel_admin_api`  
**Command:** `php artisan db:seed`

---

## ✅ All Seeders Created!

I've created comprehensive seeders for all your database tables:

1. ✅ **CategorySeeder** - 10 categories
2. ✅ **JobCategorySeeder** - 8 job categories  
3. ✅ **HomeownerSeeder** - 10 homeowners (5 with test credentials)
4. ✅ **TradieSeeder** - 10 tradies (5 with test credentials, all active/available/verified)
5. ✅ **ServiceSeeder** - ~20-30 services (2-3 per homeowner)
6. ✅ **JobRequestSeeder** - ~10-20 job requests
7. ✅ **TradieServiceSeeder** - Links tradies to services
8. ✅ **BookingSeeder** - ~20-40 bookings (2-4 per homeowner)

---

## 🎯 Quick Start

### Step 1: Navigate to Laravel Directory

```bash
cd C:\Users\Ricardo\fixo_laravel\laravel_admin_api
```

### Step 2: Run Seeders

**Option A: Fresh Database + Seed (Recommended)**
```bash
php artisan migrate:fresh --seed
```

**Option B: Just Seed (if tables exist)**
```bash
php artisan db:seed
```

That's it! Everything will be populated automatically in the correct order.

---

## 📊 What You'll Get

### Test Credentials

**Homeowners:**
- `john.smith@example.com` / `password123`
- `sarah.johnson@example.com` / `password123`
- `mike.williams@example.com` / `password123`
- `emma.brown@example.com` / `password123`
- `david.davis@example.com` / `password123`

**Tradies:**
- `tom.plumber@example.com` / `password123`
- `electric.master@example.com` / `password123`
- `carpenter.pro@example.com` / `password123`
- `paint.expert@example.com` / `password123`
- `roof.specialist@example.com` / `password123`

### Data Created

- ✅ **10 Categories** (Plumbing, Electrical, etc.)
- ✅ **8 Job Categories** (for job requests)
- ✅ **10 Homeowners** (with addresses, locations)
- ✅ **10 Tradies** (all active, available, verified)
- ✅ **~25 Services** (homeowner job requests)
- ✅ **~15 Job Requests** (for tradie recommendations)
- ✅ **~35 Tradie-Service Links** (tradies can handle services)
- ✅ **~30 Bookings** (past and future, various statuses)

---

## 🧪 Test It!

1. **Seed the database:**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Start Laravel server:**
   ```bash
   php artisan serve
   ```

3. **Login in Flutter app:**
   - Email: `john.smith@example.com`
   - Password: `password123`

4. **Check "My Bookings":**
   - Should see multiple bookings!

5. **Check "My Jobs":**
   - Should see service requests!

6. **Find Tradies:**
   - Create a service
   - Get recommendations
   - Should see available tradies!

---

## 🔍 Verify Seeding

### Check Counts in Tinker:

```bash
php artisan tinker

\App\Models\Category::count();        // Should be 10
\App\Models\Homeowner::count();        // Should be 10
\App\Models\Tradie::count();          // Should be 10
\App\Models\Service::count();         // Should be ~25
\App\Models\Booking::count();         // Should be ~30
```

### Check in PostgreSQL:

```sql
SELECT 
    'categories' as table_name, COUNT(*) as count FROM categories
UNION ALL
SELECT 'homeowners', COUNT(*) FROM homeowners
UNION ALL
SELECT 'tradies', COUNT(*) FROM tradies
UNION ALL
SELECT 'services', COUNT(*) FROM services
UNION ALL
SELECT 'bookings', COUNT(*) FROM bookings;
```

---

## 🐛 If You Get Errors

### Error: "No homeowners found"
**Solution:** Seeders run in order automatically. If you see this, run:
```bash
php artisan migrate:fresh --seed
```

### Error: Foreign key constraint
**Solution:** Reset database:
```bash
php artisan migrate:fresh --seed
```

### Error: "Tradie not available"
**Solution:** TradieSeeder sets all tradies to:
- `availability_status` = 'available'
- `status` = 'active'  
- `verified_at` = now()

If still not working, check database directly.

---

## 📝 Seeder Order (Automatic)

The `DatabaseSeeder` runs them in this order:

1. Categories (no dependencies)
2. JobCategories (no dependencies)
3. Homeowners (no dependencies)
4. Tradies (no dependencies)
5. Services (needs homeowners + categories)
6. JobRequests (needs homeowners + job_categories)
7. TradieServices (needs tradies + services)
8. Bookings (needs homeowners + tradies + services)

**You don't need to worry about order - it's automatic!**

---

## ✅ Success Output

You should see:

```
🌱 Starting database seeding...

📦 Seeding Categories...
✅ Created 10 categories

📦 Seeding Job Categories...
✅ Created 8 job categories

👤 Seeding Homeowners...
✅ Created 10 homeowners
📧 Test login: john.smith@example.com / password123

🔧 Seeding Tradies...
✅ Created 10 tradies
📧 Test tradie login: tom.plumber@example.com / password123

📋 Seeding Services...
✅ Created 25 services for homeowners

📝 Seeding Job Requests...
✅ Created 15 job requests

🔗 Linking Tradies to Services...
✅ Created 35 tradie-service relationships

📅 Seeding Bookings...
✅ Created 30 bookings

✅ Database seeding completed successfully!

📧 Test Credentials:
   Homeowner: john.smith@example.com / password123
   Tradie: tom.plumber@example.com / password123
```

---

## 🎉 Ready to Test!

Everything is set up. Just run:

```bash
php artisan migrate:fresh --seed
```

Then test in your Flutter app! 🚀

---

**Status:** ✅ **All Seeders Ready - One Command Does Everything!**

