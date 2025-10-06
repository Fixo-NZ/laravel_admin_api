<?php

namespace Database\Factories;

use App\Models\JobRequest;
use App\Models\Homeowner;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobRequestFactory extends Factory
{
    protected $model = JobRequest::class;

    public function definition()
    {
        return [
            'homeowner_id' => Homeowner::factory(),
            'service_type' => $this->faker->word(),
            'location' => $this->faker->city(),
            'budget' => $this->faker->randomFloat(2, 50, 1000),
            'description' => $this->faker->sentence(),
        ];
    }
}
