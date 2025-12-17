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

        // Ensure we end up with 20 tradies total (including presets)
        $target = 20;

        $created = 0;

        // use Faker to generate additional tradies if needed
        $faker = \Faker\Factory::create();

        // If homeowners are keyed by city, get list of available cities
        $availableCities = $homeowners->keys()->all();

        // Fill up $tradiesData with generated entries until target reached
        $i = 0;
        while (count($tradiesData) < $target) {
            $first = $faker->firstName();
            $last = $faker->lastName();
            $city = count($availableCities) ? $availableCities[array_rand($availableCities)] : $faker->city();
            $email = strtolower(preg_replace('/[^a-z0-9._-]/', '', "$first.$last$i")) . '@example.com';

            $tradiesData[] = [
                'first_name' => $first,
                'middle_name' => $faker->firstName(),
                'last_name' => $last,
                'email' => $email,
                'phone' => $faker->e164PhoneNumber(),
                'business_name' => $faker->company(),
                'city' => $city,
                'region' => $faker->state(),
                'availability_status' => 'available',
                'status' => 'active',
                'verified_at' => Carbon::now(),
                'years_experience' => $faker->numberBetween(1, 25),
                'hourly_rate' => $faker->randomFloat(2, 30, 150),
                'service_radius' => $faker->numberBetween(10, 100),
            ];

            $i++;
        }

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

            $tradie = Tradie::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
            $created++;

            // Attach 2-3 services from existing services
            $services = Service::inRandomOrder()->limit(rand(2, 3))->get();
            foreach ($services as $service) {
                $tradie->services()->syncWithoutDetaching([$service->id => ['base_rate' => rand(50, 150)]]);
            }
        }

        $this->command->info("✅ Created {$created} tradies with services and coordinates");
        $this->command->info('📧 Test tradie login: tom.plumber@example.com / password123');
    }
}
