<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sacco:update-arrears')->dailyAt('00:30');
        $schedule->command('sacco:process-standing-orders')->dailyAt('00:45');
        $schedule->command('sacco:check-defaults')->dailyAt('01:00');
        $schedule->command('sacco:mark-dormant')->weeklyOn(1, '02:00');
        $schedule->command('sacco:snapshot-capital')->monthlyOn(1, '02:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
