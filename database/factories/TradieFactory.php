<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tradie>
 */
class TradieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => null,
            'bio' => fake()->optional()->paragraph(),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Auckland', 'Wellington', 'Christchurch', 'Hamilton', 'Tauranga']),
            'region' => fake()->randomElement(['Auckland', 'Wellington', 'Canterbury', 'Waikato', 'Bay of Plenty']),
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(-47, -34),
            'longitude' => fake()->longitude(166, 179),
            'business_name' => fake()->optional()->company(),
            'license_number' => fake()->optional()->regexify('[A-Z]{2}[0-9]{6}'),
            'insurance_details' => fake()->optional()->paragraph(),
            'years_experience' => fake()->numberBetween(1, 30),
            'hourly_rate' => fake()->randomFloat(2, 25, 150),
            'availability_status' => fake()->randomElement(['available', 'busy', 'unavailable']),
            'service_radius' => fake()->numberBetween(10, 100),
            'verified_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'status' => 'active',
        ];
    }
}
