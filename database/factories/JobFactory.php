<?php

namespace Database\Factories;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
=======
use App\Models\Job;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition()
    {
        return [
            'category_id' => Category::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'location' => $this->faker->city,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'status' => 'open',
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
        ];
    }
}
