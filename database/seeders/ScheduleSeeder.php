<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Homeowner;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $homeowners = Homeowner::all();

        // ✅ Check if there are any homeowners
        if ($homeowners->isEmpty()) {
            $this->command->warn('⚠️ No homeowners found! Creating one for seeding...');
            $homeowners = collect([Homeowner::factory()->create()]);
        }

        // ✅ Create 10 schedules
        foreach (range(1, 10) as $i) {
            $homeowner = $homeowners->random(); // pick a random homeowner

            Schedule::create([
                'homeowner_id'   => $homeowner->id,
                'title'          => "Schedule #$i",
                'description'    => "Discussion for project part $i",
                'job_title'      => 'Project Manager',
                'duration'       => '1 hour',
                'date'           => Carbon::now()->addDays($i)->toDateString(),
                'start_time'     => Carbon::now()->addDays($i)->setTime(10, 0),
                'end_time'       => Carbon::now()->addDays($i)->setTime(11, 0),
                'color'          => '#'.substr(md5($i), 0, 6), // random color
                'status'         => 'scheduled',
                'rescheduled_at' => null,
            ]);
        }

        $this->command->info('✅ Successfully created 10 schedules.');
    }
}
