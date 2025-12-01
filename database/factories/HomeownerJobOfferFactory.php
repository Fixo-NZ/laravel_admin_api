<?php

namespace Database\Factories;

use App\Models\HomeownerJobOffer;
use App\Models\Homeowner;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class HomeownerJobOfferFactory extends Factory
{
    protected $model = HomeownerJobOffer::class;

    public function definition(): array
    {
        return [
            'homeowner_id' => Homeowner::factory(),
            'service_category_id' => ServiceCategory::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph,
            'job_type' => $this->faker->randomElement(['standard','recurrent']),
            'frequency' => null,
            'start_date' => null,
            'end_date' => null,
            'preferred_date' => $this->faker->date(), // Used for standard jobs
            'job_size' => $this->faker->randomElement(['small', 'medium', 'large']),
            'address' => $this->faker->address,
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'status' => 'pending',
        ];
    }

    /**
     * Define a state for recurrent jobs
     */
    public function recurrent(): static
    {
        return $this->state(fn () => [
            'job_type' => 'recurrent',
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'preferred_date' => null,
        ]);
    }
}
