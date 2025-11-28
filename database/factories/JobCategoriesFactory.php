<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\JobCategories>
 */
class JobCategoriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_name' => $this->faker->randomElement([
                'Plumbing',
                'Electrical',
                'Painting',
                'Landscaping',
                'Cleaning'
            ]),
            'description'   => $this->faker->sentence(),
            'is_active'     => true,
        ];
    }
}
