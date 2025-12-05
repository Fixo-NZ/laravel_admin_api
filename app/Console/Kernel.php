<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule your custom command every minute
        $schedule->command('send:job-reminder')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        // Load commands from app/Console/Commands
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
