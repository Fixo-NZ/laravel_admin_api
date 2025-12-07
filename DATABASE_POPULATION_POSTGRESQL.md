# Database Population Scripts for PostgreSQL

**Purpose:** Populate the database with test data for fetching tradies and booking functionality  
**Database:** PostgreSQL  
**Usage:** Run these commands in PostgreSQL shell (psql)

---

## Table of Contents

1. [Connection Instructions](#connection-instructions)
2. [Data Population Order](#data-population-order)
3. [SQL Scripts](#sql-scripts)
4. [Verification Queries](#verification-queries)

---

## Connection Instructions

### Connect to PostgreSQL

```bash
# Replace with your actual database credentials from .env
psql -h <DB_HOST> -U <DB_USERNAME> -d <DB_DATABASE>

# Example:
# psql -h localhost -U postgres -d fixo_laravel
```

### Or using environment variables:

```bash
# Windows CMD
set PGHOST=localhost
set PGUSER=postgres
set PGDATABASE=fixo_laravel
psql

# Windows PowerShell
$env:PGHOST="localhost"
$env:PGUSER="postgres"
$env:PGDATABASE="fixo_laravel"
psql
```

---

## Data Population Order

**Important:** Run scripts in this order to maintain foreign key relationships:

1. ✅ Categories
2. ✅ Job Categories
3. ✅ Homeowners
4. ✅ Tradies
5. ✅ Services (homeowner job requests)
6. ✅ Job Requests
7. ✅ Tradie Services (pivot table)
8. ✅ Bookings
9. ✅ Booking Logs

---

## SQL Scripts

### 1. Populate Categories Table

```sql
-- Insert categories for services
INSERT INTO categories (category_name, created_at, updated_at) VALUES
('Electrical', NOW(), NOW()),
('Plumbing', NOW(), NOW()),
('Building', NOW(), NOW()),
('Roofing', NOW(), NOW()),
('Painting', NOW(), NOW()),
('Landscaping', NOW(), NOW()),
('HVAC', NOW(), NOW()),
('Flooring', NOW(), NOW()),
('Carpentry', NOW(), NOW()),
('Handyman', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- Verify
SELECT * FROM categories;
```

---

### 2. Populate Job Categories Table

```sql
-- Insert job categories (for job requests)
INSERT INTO job_categories (category_name, description, is_active, created_at, updated_at) VALUES
('Electrical Work', 'All electrical related jobs', true, NOW(), NOW()),
('Plumbing Work', 'All plumbing related jobs', true, NOW(), NOW()),
('Building & Construction', 'Construction and building projects', true, NOW(), NOW()),
('Roofing Services', 'Roof installation and repairs', true, NOW(), NOW()),
('Painting Services', 'Interior and exterior painting', true, NOW(), NOW()),
('Landscaping', 'Garden and landscaping work', true, NOW(), NOW()),
('HVAC Services', 'Heating, ventilation, and air conditioning', true, NOW(), NOW()),
('Flooring Services', 'Floor installation and repairs', true, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- Verify
SELECT * FROM job_categories;
```

---

### 3. Populate Homeowners Table

```sql
-- Insert test homeowners
-- Password is bcrypt hash of "password123" (you can generate your own)
INSERT INTO homeowners (
    first_name, last_name, middle_name, email, password, phone,
    address, city, region, postal_code, latitude, longitude, status,
    created_at, updated_at
) VALUES
('John', 'Smith', 'A', 'john.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 123 4567',
 '123 Main Street', 'Auckland', 'Auckland', '1010', -36.8485, 174.7633, 'active', NOW(), NOW()),

('Sarah', 'Johnson', 'B', 'sarah.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 234 5678',
 '456 Queen Street', 'Wellington', 'Wellington', '6011', -41.2865, 174.7762, 'active', NOW(), NOW()),

('Mike', 'Williams', 'C', 'mike.williams@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 345 6789',
 '789 Victoria Street', 'Christchurch', 'Canterbury', '8011', -43.5321, 172.6362, 'active', NOW(), NOW()),

('Emma', 'Brown', 'D', 'emma.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 456 7890',
 '321 High Street', 'Hamilton', 'Waikato', '3200', -37.7870, 175.2793, 'active', NOW(), NOW()),

('David', 'Jones', 'E', 'david.jones@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 567 8901',
 '654 King Street', 'Dunedin', 'Otago', '9016', -45.8741, 170.5036, 'active', NOW(), NOW())
ON CONFLICT (email) DO NOTHING;

-- Verify
SELECT id, first_name, last_name, email, city, region FROM homeowners;
```

**Note:** The password hash above is for "password123". To generate your own:
```bash
php artisan tinker
>>> Hash::make('your_password')
```

---

### 4. Populate Tradies Table

```sql
-- Insert test tradies with various specializations and locations
-- Password is bcrypt hash of "password123"
INSERT INTO tradies (
    first_name, last_name, middle_name, email, password, phone,
    address, city, region, postal_code, latitude, longitude,
    business_name, license_number, years_experience, hourly_rate,
    availability_status, service_radius, verified_at, status,
    created_at, updated_at
) VALUES
-- Electrical Tradies (Auckland)
('James', 'Electric', 'A', 'james.electric@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 111 1111',
 '100 Electric Ave', 'Auckland', 'Auckland', '1010', -36.8500, 174.7600,
 'Auckland Electric Co', 'ELEC-001', 10, 85.00, 'available', 50, NOW(), 'active', NOW(), NOW()),

('Lisa', 'Spark', 'B', 'lisa.spark@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 222 2222',
 '200 Power Street', 'Auckland', 'Auckland', '1011', -36.8400, 174.7700,
 'Spark Electrical', 'ELEC-002', 8, 75.00, 'available', 40, NOW(), 'active', NOW(), NOW()),

-- Plumbing Tradies (Auckland & Wellington)
('Tom', 'Plumber', 'C', 'tom.plumber@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 333 3333',
 '300 Pipe Lane', 'Auckland', 'Auckland', '1012', -36.8600, 174.7500,
 'Tom''s Plumbing', 'PLUM-001', 15, 90.00, 'available', 60, NOW(), 'active', NOW(), NOW()),

('Maria', 'Water', 'D', 'maria.water@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 444 4444',
 '400 Flow Road', 'Wellington', 'Wellington', '6011', -41.2800, 174.7800,
 'Water Works Plumbing', 'PLUM-002', 12, 80.00, 'available', 50, NOW(), 'active', NOW(), NOW()),

-- Building Tradies
('Robert', 'Builder', 'E', 'robert.builder@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 555 5555',
 '500 Construction Way', 'Auckland', 'Auckland', '1013', -36.8300, 174.7800,
 'Robert''s Building', 'BUILD-001', 20, 120.00, 'available', 70, NOW(), 'active', NOW(), NOW()),

-- Painting Tradies
('Anna', 'Painter', 'F', 'anna.painter@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 666 6666',
 '600 Brush Street', 'Wellington', 'Wellington', '6012', -41.2900, 174.7700,
 'Anna''s Painting', 'PAINT-001', 7, 65.00, 'available', 45, NOW(), 'active', NOW(), NOW()),

-- HVAC Tradies
('Chris', 'HVAC', 'G', 'chris.hvac@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 777 7777',
 '700 Air Avenue', 'Christchurch', 'Canterbury', '8011', -43.5300, 172.6400,
 'Chris HVAC Services', 'HVAC-001', 9, 95.00, 'available', 55, NOW(), 'active', NOW(), NOW()),

-- Handyman (Multi-skilled)
('Alex', 'Handyman', 'H', 'alex.handyman@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 888 8888',
 '800 Fix It Road', 'Auckland', 'Auckland', '1014', -36.8500, 174.7500,
 'Alex Handyman Services', 'HANDY-001', 5, 70.00, 'available', 50, NOW(), 'active', NOW(), NOW()),

-- Busy/Unavailable Tradies (for testing)
('Busy', 'Tradie', 'I', 'busy.tradie@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 999 9999',
 '900 Busy Street', 'Auckland', 'Auckland', '1015', -36.8200, 174.7900,
 'Busy Tradie Co', 'BUSY-001', 6, 75.00, 'busy', 50, NOW(), 'active', NOW(), NOW()),

('Unavailable', 'Tradie', 'J', 'unavailable.tradie@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+64 21 000 0000',
 '1000 Unavailable Lane', 'Auckland', 'Auckland', '1016', -36.8100, 174.8000,
 'Unavailable Services', 'UNAV-001', 4, 60.00, 'unavailable', 30, NULL, 'active', NOW(), NOW())
ON CONFLICT (email) DO NOTHING;

-- Verify
SELECT id, first_name, last_name, business_name, city, availability_status, verified_at, hourly_rate FROM tradies;
```

---

### 5. Populate Services Table (Homeowner Job Requests)

```sql
-- Get category IDs first (adjust based on your actual category IDs)
-- Electrical category_id = 1, Plumbing = 2, Building = 3, etc.

-- Insert homeowner service requests
INSERT INTO services (
    homeowner_id, job_categoryid, job_description, location, status, rating,
    created_at, updated_at
) VALUES
-- Services for John Smith (homeowner_id = 1)
(1, 1, 'Need electrical installation for new kitchen lights', '123 Main Street, Auckland', 'Pending', NULL, NOW(), NOW()),
(1, 2, 'Fix leaking faucet in bathroom', '123 Main Street, Auckland', 'InProgress', NULL, NOW(), NOW()),
(1, 3, 'Build a deck in the backyard', '123 Main Street, Auckland', 'Pending', NULL, NOW(), NOW()),

-- Services for Sarah Johnson (homeowner_id = 2)
(2, 1, 'Install ceiling fan in living room', '456 Queen Street, Wellington', 'Pending', NULL, NOW(), NOW()),
(2, 4, 'Repair roof leak', '456 Queen Street, Wellington', 'Completed', 5, NOW(), NOW()),
(2, 5, 'Paint interior walls', '456 Queen Street, Wellington', 'Pending', NULL, NOW(), NOW()),

-- Services for Mike Williams (homeowner_id = 3)
(3, 2, 'Install new toilet', '789 Victoria Street, Christchurch', 'Pending', NULL, NOW(), NOW()),
(3, 7, 'Install heat pump system', '789 Victoria Street, Christchurch', 'InProgress', NULL, NOW(), NOW()),
(3, 8, 'Install hardwood flooring', '789 Victoria Street, Christchurch', 'Pending', NULL, NOW(), NOW()),

-- Services for Emma Brown (homeowner_id = 4)
(4, 6, 'Design and install garden landscaping', '321 High Street, Hamilton', 'Pending', NULL, NOW(), NOW()),
(4, 1, 'Upgrade electrical panel', '321 High Street, Hamilton', 'Completed', 4, NOW(), NOW()),

-- Services for David Jones (homeowner_id = 5)
(5, 2, 'Replace all plumbing pipes', '654 King Street, Dunedin', 'Pending', NULL, NOW(), NOW()),
(5, 5, 'Exterior house painting', '654 King Street, Dunedin', 'Pending', NULL, NOW(), NOW())
RETURNING id, homeowner_id, job_categoryid, job_description, status;

-- Verify
SELECT s.id, h.first_name || ' ' || h.last_name as homeowner, c.category_name, s.job_description, s.status 
FROM services s
JOIN homeowners h ON s.homeowner_id = h.id
JOIN categories c ON s.job_categoryid = c.id;
```

**Note:** Adjust the `homeowner_id` and `job_categoryid` values based on the actual IDs from your database.

---

### 6. Populate Job Requests Table

```sql
-- Insert job requests (for tradie recommendation testing)
-- Get job_category_id from job_categories table (usually 1-8)
-- Get homeowner_id from homeowners table (usually 1-5)

INSERT INTO job_requests (
    job_category_id, homeowner_id, title, description, job_type, status,
    budget, location, latitude, longitude, scheduled_at, created_at, updated_at
) VALUES
-- Job Request 1: Electrical work in Auckland
(1, 1, 'Kitchen Lighting Installation', 'Need professional to install LED lights in kitchen', 'standard', 'pending',
 500.00, '123 Main Street, Auckland', -36.8485, 174.7633, NULL, NOW(), NOW()),

-- Job Request 2: Plumbing work in Auckland
(2, 1, 'Bathroom Faucet Repair', 'Fix leaking faucet in master bathroom', 'urgent', 'pending',
 200.00, '123 Main Street, Auckland', -36.8485, 174.7633, NULL, NOW(), NOW()),

-- Job Request 3: Building work in Auckland
(3, 1, 'Deck Construction', 'Build a 5x3 meter deck in backyard', 'standard', 'pending',
 3000.00, '123 Main Street, Auckland', -36.8485, 174.7633, '2025-12-20 09:00:00', NOW(), NOW()),

-- Job Request 4: Electrical work in Wellington
(1, 2, 'Ceiling Fan Installation', 'Install ceiling fan in living room', 'standard', 'pending',
 400.00, '456 Queen Street, Wellington', -41.2865, 174.7762, NULL, NOW(), NOW()),

-- Job Request 5: Roofing work in Wellington
(4, 2, 'Roof Leak Repair', 'Fix leak in roof above bedroom', 'urgent', 'active',
 800.00, '456 Queen Street, Wellington', -41.2865, 174.7762, '2025-12-15 10:00:00', NOW(), NOW()),

-- Job Request 6: Plumbing work in Christchurch
(2, 3, 'Toilet Installation', 'Install new modern toilet', 'standard', 'pending',
 600.00, '789 Victoria Street, Christchurch', -43.5321, 172.6362, NULL, NOW(), NOW()),

-- Job Request 7: HVAC work in Christchurch
(7, 3, 'Heat Pump Installation', 'Install heat pump system for whole house', 'standard', 'pending',
 5000.00, '789 Victoria Street, Christchurch', -43.5321, 172.6362, '2025-12-25 08:00:00', NOW(), NOW())
RETURNING id, title, homeowner_id, job_category_id, status, budget;

-- Verify
SELECT jr.id, jr.title, h.first_name || ' ' || h.last_name as homeowner, 
       jc.category_name, jr.job_type, jr.status, jr.budget
FROM job_requests jr
JOIN homeowners h ON jr.homeowner_id = h.id
JOIN job_categories jc ON jr.job_category_id = jc.id;
```

---

### 7. Populate Tradie Services Table (Pivot Table)

```sql
-- Link tradies to services (homeowner job requests) they can handle
-- This is important for tradie recommendations

-- Get tradie IDs and service IDs first, then link them
-- Tradie 1 (James Electric) - can handle electrical services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 1, id, 85.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 1  -- Electrical category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 2 (Lisa Spark) - can handle electrical services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 2, id, 75.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 1  -- Electrical category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 3 (Tom Plumber) - can handle plumbing services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 3, id, 90.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 2  -- Plumbing category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 4 (Maria Water) - can handle plumbing services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 4, id, 80.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 2  -- Plumbing category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 5 (Robert Builder) - can handle building services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 5, id, 120.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 3  -- Building category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 6 (Anna Painter) - can handle painting services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 6, id, 65.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 5  -- Painting category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 7 (Chris HVAC) - can handle HVAC services
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 7, id, 95.00, NOW(), NOW()
FROM services
WHERE job_categoryid = 7  -- HVAC category
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Tradie 8 (Alex Handyman) - can handle multiple categories
INSERT INTO tradie_services (tradie_id, service_id, base_rate, created_at, updated_at)
SELECT 8, id, 70.00, NOW(), NOW()
FROM services
WHERE job_categoryid IN (1, 2, 5, 10)  -- Multiple categories
ON CONFLICT (tradie_id, service_id) DO NOTHING;

-- Verify tradie-service relationships
SELECT 
    t.id as tradie_id,
    t.first_name || ' ' || t.last_name as tradie_name,
    s.id as service_id,
    c.category_name,
    s.job_description,
    ts.base_rate
FROM tradie_services ts
JOIN tradies t ON ts.tradie_id = t.id
JOIN services s ON ts.service_id = s.id
JOIN categories c ON s.job_categoryid = c.id
ORDER BY t.id, s.id;
```

---

### 8. Populate Bookings Table

```sql
-- Create bookings linking homeowners, tradies, and services
-- Get IDs from previous inserts

INSERT INTO bookings (
    homeowner_id, tradie_id, service_id, booking_start, booking_end, status, total_price,
    created_at, updated_at
) VALUES
-- Booking 1: John Smith booked James Electric for electrical work
(1, 1, 1, '2025-12-18 09:00:00', '2025-12-18 12:00:00', 'confirmed', 255.00, NOW(), NOW()),

-- Booking 2: Sarah Johnson booked Maria Water for plumbing
(2, 4, 4, '2025-12-19 10:00:00', '2025-12-19 14:00:00', 'pending', 320.00, NOW(), NOW()),

-- Booking 3: Mike Williams booked Chris HVAC for HVAC installation
(3, 7, 8, '2025-12-20 08:00:00', '2025-12-20 17:00:00', 'pending', 475.00, NOW(), NOW()),

-- Booking 4: Emma Brown booked Anna Painter for painting
(4, 6, 10, '2025-12-21 09:00:00', '2025-12-21 16:00:00', 'confirmed', 455.00, NOW(), NOW()),

-- Booking 5: David Jones booked Tom Plumber for plumbing
(5, 3, 12, '2025-12-22 10:00:00', '2025-12-22 15:00:00', 'pending', 450.00, NOW(), NOW()),

-- Past booking (completed)
(1, 2, 2, '2025-12-10 09:00:00', '2025-12-10 11:00:00', 'completed', 150.00, NOW(), NOW()),

-- Past booking (canceled)
(2, 5, 5, '2025-12-11 10:00:00', '2025-12-11 18:00:00', 'canceled', 960.00, NOW(), NOW())
RETURNING id, homeowner_id, tradie_id, service_id, booking_start, status;

-- Verify bookings
SELECT 
    b.id,
    h.first_name || ' ' || h.last_name as homeowner,
    t.first_name || ' ' || t.last_name as tradie,
    s.job_description as service,
    b.booking_start,
    b.booking_end,
    b.status,
    b.total_price
FROM bookings b
JOIN homeowners h ON b.homeowner_id = h.id
JOIN tradies t ON b.tradie_id = t.id
JOIN services s ON b.service_id = s.id
ORDER BY b.booking_start DESC;
```

---

### 9. Populate Booking Logs Table

```sql
-- Insert booking logs for audit trail
INSERT INTO booking_logs (
    booking_id, user_id, action, notes, created_at, updated_at
) VALUES
(1, 1, 'created', 'Booking created by homeowner', NOW(), NOW()),
(1, 1, 'updated', 'Booking confirmed by tradie', NOW(), NOW()),
(2, 2, 'created', 'Booking created by homeowner', NOW(), NOW()),
(3, 3, 'created', 'Booking created by homeowner', NOW(), NOW()),
(4, 4, 'created', 'Booking created by homeowner', NOW(), NOW()),
(4, 4, 'updated', 'Booking confirmed', NOW(), NOW()),
(5, 5, 'created', 'Booking created by homeowner', NOW(), NOW()),
(6, 1, 'created', 'Booking created', NOW(), NOW()),
(6, 1, 'updated', 'Booking completed', NOW(), NOW()),
(7, 2, 'created', 'Booking created', NOW(), NOW()),
(7, 2, 'canceled', 'Booking canceled by homeowner', NOW(), NOW())
RETURNING id, booking_id, action, notes;

-- Verify
SELECT bl.id, b.id as booking_id, bl.action, bl.notes, bl.created_at
FROM booking_logs bl
JOIN bookings b ON bl.booking_id = b.id
ORDER BY bl.created_at DESC;
```

---

## Verification Queries

### Check All Data Counts

```sql
-- Count records in each table
SELECT 
    'categories' as table_name, COUNT(*) as count FROM categories
UNION ALL
SELECT 'job_categories', COUNT(*) FROM job_categories
UNION ALL
SELECT 'homeowners', COUNT(*) FROM homeowners
UNION ALL
SELECT 'tradies', COUNT(*) FROM tradies
UNION ALL
SELECT 'services', COUNT(*) FROM services
UNION ALL
SELECT 'job_requests', COUNT(*) FROM job_requests
UNION ALL
SELECT 'tradie_services', COUNT(*) FROM tradie_services
UNION ALL
SELECT 'bookings', COUNT(*) FROM bookings
UNION ALL
SELECT 'booking_logs', COUNT(*) FROM booking_logs;
```

### Test Tradie Recommendation Query

```sql
-- Test query similar to TradieRecommendationController
-- Replace jobId with an actual job_request id (e.g., 1)
WITH job_details AS (
    SELECT 
        jr.id,
        jr.latitude,
        jr.longitude,
        jr.budget,
        jr.job_category_id
    FROM job_requests jr
    WHERE jr.id = 1  -- Change this to test different jobs
)
SELECT 
    t.id,
    t.first_name || ' ' || t.last_name as name,
    t.business_name,
    t.hourly_rate,
    t.availability_status,
    t.service_radius,
    t.city,
    t.region,
    -- Calculate distance (simplified)
    (
        6371 * acos(
            cos(radians(jd.latitude)) * cos(radians(t.latitude)) * 
            cos(radians(t.longitude) - radians(jd.longitude)) + 
            sin(radians(jd.latitude)) * sin(radians(t.latitude))
        )
    ) AS distance_km
FROM tradies t
CROSS JOIN job_details jd
WHERE t.status = 'active'
  AND t.availability_status = 'available'
  AND t.verified_at IS NOT NULL
  AND EXISTS (
      SELECT 1 
      FROM tradie_services ts
      JOIN services s ON ts.service_id = s.id
      WHERE ts.tradie_id = t.id
        AND s.job_categoryid = jd.job_category_id
  )
ORDER BY distance_km
LIMIT 5;
```

### Test Booking Query

```sql
-- Get all bookings with details
SELECT 
    b.id as booking_id,
    h.first_name || ' ' || h.last_name as homeowner,
    t.first_name || ' ' || t.last_name as tradie,
    s.job_description,
    b.booking_start,
    b.booking_end,
    b.status,
    b.total_price,
    CASE 
        WHEN b.booking_start > NOW() THEN 'Upcoming'
        ELSE 'Past'
    END as booking_type
FROM bookings b
JOIN homeowners h ON b.homeowner_id = h.id
JOIN tradies t ON b.tradie_id = t.id
JOIN services s ON b.service_id = s.id
ORDER BY b.booking_start DESC;
```

---

## Quick Setup Script (All-in-One)

If you want to run everything at once, save this as `populate_database.sql`:

```sql
-- Run all population scripts in sequence
\echo 'Populating Categories...'
\i 1_categories.sql

\echo 'Populating Job Categories...'
\i 2_job_categories.sql

\echo 'Populating Homeowners...'
\i 3_homeowners.sql

\echo 'Populating Tradies...'
\i 4_tradies.sql

\echo 'Populating Services...'
\i 5_services.sql

\echo 'Populating Job Requests...'
\i 6_job_requests.sql

\echo 'Populating Tradie Services...'
\i 7_tradie_services.sql

\echo 'Populating Bookings...'
\i 8_bookings.sql

\echo 'Populating Booking Logs...'
\i 9_booking_logs.sql

\echo 'Database population complete!'
```

Or run directly in psql:

```bash
psql -h localhost -U postgres -d fixo_laravel -f populate_database.sql
```

---

## Important Notes

1. **Password Hashes:** All test users have password: `password123`
   - Hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

2. **ID References:** The scripts assume sequential IDs starting from 1. If your database has different IDs, adjust the foreign key references accordingly.

3. **Coordinates:** All coordinates are in New Zealand (Auckland, Wellington, Christchurch, etc.)

4. **Dates:** Booking dates are set for December 2025. Adjust as needed.

5. **ON CONFLICT:** Most INSERT statements use `ON CONFLICT DO NOTHING` to prevent errors if data already exists.

---

## Troubleshooting

### Foreign Key Violations

If you get foreign key errors, check the order of inserts:
1. Categories must exist before Services
2. Homeowners must exist before Services and Bookings
3. Tradies must exist before Tradie Services and Bookings
4. Services must exist before Tradie Services and Bookings

### Check Existing Data

```sql
-- Check if data already exists
SELECT COUNT(*) FROM categories;
SELECT COUNT(*) FROM homeowners;
SELECT COUNT(*) FROM tradies;
SELECT COUNT(*) FROM services;
```

### Clear All Test Data (⚠️ DANGER)

```sql
-- ⚠️ WARNING: This deletes ALL data!
TRUNCATE TABLE booking_logs CASCADE;
TRUNCATE TABLE bookings CASCADE;
TRUNCATE TABLE tradie_services CASCADE;
TRUNCATE TABLE job_requests CASCADE;
TRUNCATE TABLE services CASCADE;
TRUNCATE TABLE tradies CASCADE;
TRUNCATE TABLE homeowners CASCADE;
TRUNCATE TABLE job_categories CASCADE;
TRUNCATE TABLE categories CASCADE;
```

---

**End of Documentation**

