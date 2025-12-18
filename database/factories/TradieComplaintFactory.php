<?php

namespace Database\Factories;

use App\Models\TradieComplaint;
use App\Models\Tradie;
use App\Models\Homeowner;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradieComplaintFactory extends Factory
{
    protected $model = TradieComplaint::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'approved', 'dismissed']);

        return [
            'tradie_id' => Tradie::factory(),
            'homeowner_id' => $this->faker->boolean(80) ? Homeowner::factory() : null,
            'title' => $this->faker->randomElement([
                'Poor workmanship on kitchen renovation',
                'Incomplete plumbing job',
                'Unprofessional behavior',
                'Failed to show up for scheduled appointment',
                'Overcharged for materials',
                'Damaged property during work',
                'Work not up to building code standards',
                'Poor communication and delays',
                'Used substandard materials',
                'Left job site messy and unsafe'
            ]),
            'description' => $this->faker->paragraphs(3, true),
            'status' => $status,
            'reviewed_at' => $status !== 'pending' ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'reviewed_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
            'reviewed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function dismissed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'dismissed',
            'reviewed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function withoutHomeowner(): static
    {
        return $this->state(fn(array $attributes) => [
            'homeowner_id' => null,
        ]);
    }
}
