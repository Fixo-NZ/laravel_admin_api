<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'job_id' => null,
            'homeowner_id' => null,
            'tradie_id' => null,
            'rating' => $this->faker->numberBetween(1, 5),
            'feedback' => $this->faker->paragraph(),
            'service_quality_rating' => $this->faker->optional()->numberBetween(1, 5),
            'service_quality_comment' => $this->faker->optional()->sentence(),
            'performance_rating' => $this->faker->optional()->numberBetween(1, 5),
            'performance_comment' => $this->faker->optional()->sentence(),
            'contractor_service_rating' => $this->faker->optional()->numberBetween(1, 5),
            'response_time_rating' => $this->faker->optional()->numberBetween(1, 5),
            'best_feature' => $this->faker->optional()->word(),
            'images' => [],
            'show_username' => $this->faker->boolean(80),
            'helpful_count' => $this->faker->numberBetween(0, 50),
            'status' => 'approved',
        ];
    }

    public function withHomeowner(): static
    {
        return $this->state(fn (array $attributes) => [
            'homeowner_id' => Homeowner::factory(),
        ]);
    }

    public function withTradie(): static
    {
        return $this->state(fn (array $attributes) => [
            'tradie_id' => Tradie::factory(),
        ]);
    }

    public function withJob(): static
    {
        return $this->state(fn (array $attributes) => [
            'job_id' => 1, // Simplified for testing
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function withImages(): static
    {
        return $this->state(fn (array $attributes) => [
            'images' => [
                'reviews/image1.jpg',
                'reviews/image2.jpg',
            ],
        ]);
    }
}
