<?php

namespace Database\Seeders;

use App\Models\Homeowner;
use App\Models\JobCategories;
use App\Models\Service;
use App\Models\JobRequest;
use App\Models\Tradie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BookingTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates test data for the booking flow:
     * - A homeowner
     * - Job categories
     * - A service (for urgent booking)
     * - A job request (for recommendations)
     * - A tradie (available, verified, with location)
     * - Links tradie to service
     */
    public function run(): void
    {
        // 1. Create or get job categories
        $electricalCategory = JobCategories::firstOrCreate(
            ['category_name' => 'Electrical'],
            ['description' => 'Electrical services and repairs', 'is_active' => true]
        );

        $plumbingCategory = JobCategories::firstOrCreate(
            ['category_name' => 'Plumbing'],
            ['description' => 'Plumbing installation and repairs', 'is_active' => true]
        );

        // 2. Create or get a test homeowner
        $homeowner = Homeowner::firstOrCreate(
            ['email' => 'test.homeowner@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Homeowner',
                'phone' => '0211234567',
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]
        );

        // 3. Create a service (for urgent booking flow)
        $service = Service::firstOrCreate(
            [
                'homeowner_id' => $homeowner->id,
                'job_categoryid' => $electricalCategory->id,
            ],
            [
                'job_description' => 'Need urgent electrical repair - lights not working in kitchen',
                'location' => '123 Main Street, Auckland',
                'status' => 'Pending',
            ]
        );

        // 4. Create a JobRequest (REQUIRED for recommendations to work)
        // This needs location data (latitude/longitude) for distance calculations
        $jobRequest = JobRequest::firstOrCreate(
            [
                'homeowner_id' => $homeowner->id,
                'job_category_id' => $electricalCategory->id,
            ],
            [
                'title' => 'Urgent Electrical Repair',
                'description' => 'Need urgent electrical repair - lights not working in kitchen',
                'job_type' => 'urgent',
                'status' => 'pending',
                'budget' => 150.00,
                'location' => '123 Main Street, Auckland',
                'latitude' => -36.8485,  // Auckland coordinates
                'longitude' => 174.7633,
            ]
        );

        // 5. Create a tradie (must be active, available, verified, with location)
        $tradie = Tradie::firstOrCreate(
            ['email' => 'john.electrician@test.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Electrician',
                'middle_name' => 'M',
                'phone' => '0219876543',
                'password' => Hash::make('password123'),
                'city' => 'Auckland',
                'region' => 'Auckland',
                'latitude' => -36.8500,  // Close to job location
                'longitude' => 174.7650,
                'business_name' => 'John\'s Electrical Services',
                'years_experience' => 10,
                'hourly_rate' => 80.00,
                'availability_status' => 'available',
                'service_radius' => 50,  // 50 km radius
                'verified_at' => now(),  // IMPORTANT: Must be verified
                'status' => 'active',
            ]
        );

        // 6. Link tradie to service via pivot table
        // First, check if link already exists
        $pivotExists = DB::table('tradie_services')
            ->where('tradie_id', $tradie->id)
            ->where('service_id', $service->id)
            ->exists();

        if (!$pivotExists) {
            DB::table('tradie_services')->insert([
                'service_id' => $service->id,
                'tradie_id' => $tradie->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Test data created:');
        $this->command->info("   Homeowner ID: {$homeowner->id} (email: {$homeowner->email})");
        $this->command->info("   Service ID: {$service->id}");
        $this->command->info("   JobRequest ID: {$jobRequest->id}");
        $this->command->info("   Tradie ID: {$tradie->id} (email: {$tradie->email})");
        $this->command->info('');
        $this->command->info('📱 In Flutter app:');
        $this->command->info("   - Login as: {$homeowner->email} / password123");
        $this->command->info("   - Go to Urgent Booking");
        $this->command->info("   - Select Service ID: {$service->id}");
        $this->command->info("   - View Recommendations - should show tradie");
    }
}

