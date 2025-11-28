<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Homeowner;
use App\Models\JobCategories;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\JobRequest>
 */
class JobRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'homeowner_id'    => Homeowner::factory(),      // ✅ uses HomeownerFactory
            'job_category_id' => JobCategories::factory(),  // ✅ uses JobCategoriesFactory
            'title'           => $this->faker->sentence(4),
            'description'     => $this->faker->paragraph(),
            'job_type'        => $this->faker->randomElement(['urgent', 'standard', 'recurring']),
            'status'          => $this->faker->randomElement(['pending', 'active', 'completed', 'cancelled']),
            'budget'          => $this->faker->optional()->randomFloat(2, 100, 5000),
            'location'        => $this->faker->address(),
            'latitude'        => $this->faker->latitude(-47, -34),
            'longitude'       => $this->faker->longitude(166, 179),
            'scheduled_at'    => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
