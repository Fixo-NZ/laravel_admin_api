<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        Schedule::create([
            'title' => 'Team Meeting',
            'description' => 'Discuss project milestones',
            'start_time' => Carbon::now()->addDays(1)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(1)->setTime(11, 0),
            'color' => '#ff0000',
        ]);

        Schedule::create([
            'title' => 'Doctor Appointment',
            'description' => 'Regular checkup',
            'start_time' => Carbon::now()->addDays(2)->setTime(15, 0),
            'end_time' => Carbon::now()->addDays(2)->setTime(16, 0),
            'color' => '#00ff00',
        ]);
    }
}
