<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Homeowner>
 */
class HomeownerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
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
            'status' => 'active',
        ];
    }
}
