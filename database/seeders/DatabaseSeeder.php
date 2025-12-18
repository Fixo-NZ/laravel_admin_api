<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Homeowner;
use App\Models\Tradie;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run in order to respect dependencies:
     * 1. Categories (no dependencies)
     * 2. JobCategories (no dependencies)
     * 3. Homeowners (no dependencies)
     * 4. Tradies (no dependencies)
     * 5. Services (needs homeowners + categories)
     * 6. JobRequests (needs homeowners + job_categories)
     * 7. TradieServices (needs tradies + services)
     * 8. Bookings (needs homeowners + tradies + services)
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();
    

        // Step 1: Categories (used by services)
        $this->command->info('📦 Seeding Categories...');
        $this->call(CategorySeeder::class);
        $this->command->newLine();

        // Step 2: Job Categories (used by job_requests)
        $this->command->info('📦 Seeding Job Categories...');
        $this->call(JobCategorySeeder::class);
        $this->command->newLine();

                        // Step 3: Homeowners
        $this->command->info('👤 Seeding Homeowners...');
        $this->call(HomeownerSeeder::class);
        $this->command->newLine();

                // Step 4: Tradies
        $this->command->info('🔧 Seeding Tradies...');
        $this->call(TradieSeeder::class);
        $this->command->newLine();

                // Step 5: Services (needs homeowners + categories)
        $this->command->info('📋 Seeding Services...');
        $this->call(ServiceSeeder::class);
        $this->command->newLine();


        // Step 6: Job Requests (needs homeowners + job_categories)
        $this->command->info('📝 Seeding Job Requests...');
        $this->call(JobRequestSeeder::class);
        $this->command->newLine();

        // Step 7: Tradie Services (pivot table - needs tradies + services)
        $this->command->info('🔗 Linking Tradies to Services...');
        $this->call(TradieServiceSeeder::class);
        $this->command->newLine();

        // Step 8: Bookings (needs homeowners + tradies + services)
        $this->command->info('📅 Seeding Bookings...');
        
        // Seed bookings after homeowners, tradies and services exist
        $this->call(BookingSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📧 Test Credentials:');
        $this->command->info('   Homeowner: john.smith@example.com / password123');
        $this->command->info('   Tradie: tom.plumber@example.com / password123');
    }
}
