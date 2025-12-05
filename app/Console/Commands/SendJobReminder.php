<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HomeownerJobOffer;

class SendJobReminder extends Command
{
    protected $signature = 'send:job-reminder';
    protected $description = 'Send push notifications to tradies for upcoming jobs';

    public function handle()
    {
        $upcomingJobs = HomeownerJobOffer::with('tradie', 'homeowner')
            ->whereIn('status', ['accepted', 'in_progress'])
            ->whereBetween('start_time', [now()->subMinute(), now()->addMinute()])
            ->get();

        foreach ($upcomingJobs as $job) {
            app(\App\Http\Controllers\ScheduleController::class)
                ->sendJobReminderToTradie($job);
        }

        $this->info('Job reminders sent successfully.');
    }
}
