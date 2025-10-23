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
            'homeowner_id'   => 1, // ✅ Assign to a homeowner
            'title'          => 'Team Meeting',
            'description'    => 'Discuss project milestones and next sprint goals',
            'job_title'      => 'Project Manager',
            'duration'       => '1 hour',
            'date'           => Carbon::now()->addDays(1)->toDateString(),
            'start_time'     => Carbon::now()->addDays(1)->setTime(10, 0),
            'end_time'       => Carbon::now()->addDays(1)->setTime(11, 0),
            'color'          => '#ff0000',
            'status'         => 'scheduled',
            'rescheduled_at' => null,
        ]);
    }
}
