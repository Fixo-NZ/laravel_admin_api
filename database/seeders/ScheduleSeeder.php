<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Homeowner;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $homeowners = Homeowner::all();

        if ($homeowners->isEmpty()) {
            $homeowners = collect([Homeowner::factory()->create()]);
        }

        // Create 10 schedules, each linked to a random homeowner
        Schedule::factory(10)->create([
            'homeowner_id' => fn() => $homeowners->random()->id,
        ]);
    }
}
