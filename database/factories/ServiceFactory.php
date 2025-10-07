<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Homeowner;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition()
    {
        return [
            'homeowner_id' => Homeowner::factory(),
            'job_categoryid' => Category::factory(),
            'job_description' => $this->faker->sentence,
            'location' => $this->faker->city,
            'status' => $this->faker->randomElement(['Pending', 'InProgress', 'Completed', 'Cancelled']),
            'createdAt' => now(),
            'updatedAt' => now(),
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }
}
