<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 hour', '+1 week');
        $end = (clone $start)->modify('+1 hour');

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_time' => $start,
            'end_time' => $end,
            'color' => $this->faker->hexColor(),
            'status' => $this->faker->randomElement(['scheduled', 'rescheduled', 'cancelled']),
            'rescheduled_at' => $this->faker->boolean(30) ? Carbon::now()->subDays(rand(1, 5)) : null,
        ];
    }
}
