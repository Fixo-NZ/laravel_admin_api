# 📅 Schedule API for Flutter Frontend

This module provides API endpoints for managing schedules in the calendar system.  
You can **fetch**, **reschedule**, and **cancel** schedule entries directly from the Flutter frontend.

---

## ⚙️ Setup Instructions

### 1. Run Database Seeder

To generate sample schedule data, run the following:

```bash

php artisan db:seed

after doing that do 
php artisan db:seed --class=ServiceSeeder
php artisan db:seed --class=HomeownerjobOfferSeeder

Then for postman api

php artisan tinker

$tradie = App\Models\Tradie::find(1);
$token = $tradie->createToken('PostmanToken')->plainTextToken;
echo $token;

to test

php artisan schedule:work

FIREBASE_CREDENTIALS="storage/firebase/service-account.json"
.env

php artisan send:job-reminder
