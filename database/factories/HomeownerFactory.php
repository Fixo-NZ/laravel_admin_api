<?php

namespace Database\Factories;

use App\Models\Homeowner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class HomeownerFactory extends Factory
{
    protected $model = Homeowner::class;

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
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
        ];

    }
}
