<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tradie;
use App\Models\Skill;
use App\Models\Category;
use App\Models\Job;
use Faker\Factory as Faker;

class RealisticNZTradieSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_NZ');

        // Create categories
        $categories = [
            'Plumbing', 'Electrical', 'Carpentry', 'Painting', 'Roofing', 'Landscaping', 'Tiling', 'Flooring', 'Glazing', 'Bricklaying'
        ];
        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat] = Category::create(['category_name' => $cat]);
        }

        // Create tradies
        foreach (range(1, 20) as $i) {
            $cat = $faker->randomElement($categories);
            $tradie = Tradie::create([
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'middle_name' => $faker->firstName(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'avatar' => null,
                'bio' => $faker->sentence(),
                'address' => $faker->streetAddress(),
                'city' => $faker->randomElement(['Auckland', 'Wellington', 'Christchurch', 'Hamilton', 'Tauranga']),
                'region' => $faker->randomElement(['Auckland', 'Wellington', 'Canterbury', 'Waikato', 'Bay of Plenty']),
                'postal_code' => $faker->postcode(),
                'latitude' => $faker->latitude(-47, -34),
                'longitude' => $faker->longitude(166, 179),
                'business_name' => $faker->company(),
                'license_number' => $faker->optional()->regexify('[A-Z]{2}[0-9]{6}'),
                'insurance_details' => $faker->optional()->sentence(),
                'years_experience' => $faker->numberBetween(1, 40),
                'hourly_rate' => $faker->randomFloat(2, 30, 150),
                'availability_status' => $faker->randomElement(['available', 'busy', 'unavailable']),
                'service_radius' => $faker->numberBetween(10, 100),
                'verified_at' => $faker->optional(0.7)->dateTimeBetween('-2 years', 'now'),
                'status' => 'active',
                'rating' => $faker->randomFloat(2, 3.0, 5.0),
            ]);
            Skill::create([
                'tradie_id' => $tradie->id,
                'skill_name' => $cat,
            ]);
        }

        // Create jobs
        foreach (range(1, 10) as $i) {
            $cat = $faker->randomElement($categories);
            Job::create([
                'category_id' => $categoryModels[$cat]->id,
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph(),
                'location' => $faker->streetAddress(),
                'latitude' => $faker->latitude(-47, -34),
                'longitude' => $faker->longitude(166, 179),
                'status' => 'open',
                'budget_min' => $faker->randomFloat(2, 40, 80),
                'budget_max' => $faker->randomFloat(2, 81, 150),
            ]);
        }
    }
}
