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
            'first_name'  => fake()->firstName(),
            'last_name'   => fake()->lastName(),
            'middle_name' => fake()->firstName(),
            'email'       => fake()->unique()->safeEmail(),
            'phone'       => fake()->phoneNumber(),
            'password'    => Hash::make('password123'), // default test password
            'address'     => fake()->streetAddress(),
            'city'        => fake()->city(),
            'region'      => fake()->state(),
            'postal_code' => fake()->postcode(),
            'status'      => fake()->randomElement(['active', 'inactive', 'suspended']),
        ];

    }
}
