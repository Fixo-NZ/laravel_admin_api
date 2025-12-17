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
        $jobType = $this->faker->randomElement(['standard', 'recurrent']);

        return [
            'number' => 'JOB-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),

            'homeowner_id' => Homeowner::factory(),
            'service_category_id' => ServiceCategory::factory(),

            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),

            'job_type' => $jobType,
            'job_size' => $this->faker->randomElement(['small', 'medium', 'large']),

            'budget' => $this->faker->randomFloat(2, 500, 5000),
            'final_budget' => null,

            'preferred_date' => in_array($jobType, ['standard','urgent'])
                ? $this->faker->date()
                : null,

            'frequency' => $jobType === 'recurrent'
                ? $this->faker->randomElement(['daily', 'weekly', 'monthly', 'custom'])
                : null,

            'start_date' => $jobType === 'recurrent' ? $this->faker->date() : null,
            'end_date' => $jobType === 'recurrent' ? $this->faker->date() : null,

            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(10, 14),
            'longitude' => $this->faker->longitude(120, 125),

            'status' => 'open',
        ];
    }

    /**
     * Recurrent job state
     */
    public function recurrent(): static
    {
        return $this->state(fn () => [
            'job_type' => 'recurrent',
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'preferred_date' => null,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ]);
    }
}
