<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
    /**
     * REQUIRED FIX: Override the default Faker instance to explicitly use the 'en_US' locale.
     * This guarantees that generators like 'firstName' are available, bypassing environment issues.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return FakerFactory::create('en_US');
    }

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),       // generates random first name
            'last_name' => fake()->lastName(),        // generates random last name
            'middle_name' => fake()->firstName(),     // generates random middle name
            'email' => fake()->unique()->safeEmail(),  // unique random email
            'email_verified_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'admin', // default role, change if needed
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']), 
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
