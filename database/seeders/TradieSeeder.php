<?php

namespace Database\Seeders;

use App\Models\Tradie;
use App\Models\Homeowner;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TradieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $homeowners = Homeowner::all()->keyBy('city');

        if ($homeowners->isEmpty()) {
            $this->command->error('❌ No homeowners found. Please run HomeownerSeeder first.');
            return;
        }

        $tradiesData = [
            [
                'first_name' => 'Tom',
                'middle_name' => 'Andrew',
                'last_name' => 'Plumber',
                'email' => 'tom.plumber@example.com',
                'phone' => '+64 21 111 2222',
                'business_name' => "Tom's Plumbing Services",
                'city' => 'Auckland',
                'region' => 'Auckland',
                'availability_status' => 'available',
                'status' => 'active',
                'verified_at' => Carbon::now(),
                'years_experience' => 10,
                'hourly_rate' => 75.00,
                'service_radius' => 50,
            ],
            [
                'first_name' => 'Electric',
                'middle_name' => 'Power',
                'last_name' => 'Master',
                'email' => 'electric.master@example.com',
                'phone' => '+64 21 222 3333',
                'business_name' => 'Electric Master Ltd',
                'city' => 'Wellington',
                'region' => 'Wellington',
                'availability_status' => 'available',
                'status' => 'active',
                'verified_at' => Carbon::now(),
                'years_experience' => 15,
                'hourly_rate' => 85.00,
                'service_radius' => 60,
            ],
            // Add more tradies as needed
        ];

        $created = 0;

        foreach ($tradiesData as $data) {
            $data['password'] = Hash::make('password123');

            // Assign lat/lng based on corresponding homeowner
            if (isset($homeowners[$data['city']])) {
                $home = $homeowners[$data['city']];
                $lat = $home->latitude + rand(-100, 100)/10000;
                $lng = $home->longitude + rand(-100, 100)/10000;
                $data['latitude'] = $lat;
                $data['longitude'] = $lng;
            }

            $tradie = Tradie::create($data);
            $created++;

            // Attach 2-3 services from existing services
            $services = Service::inRandomOrder()->limit(rand(2, 3))->get();
            foreach ($services as $service) {
                $tradie->services()->attach($service->id, ['base_rate' => rand(50, 150)]);
            }
        }

        $this->command->info("✅ Created {$created} tradies with services and coordinates");
        $this->command->info('📧 Test tradie login: tom.plumber@example.com / password123');
    }
}
