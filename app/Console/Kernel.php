<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('orders:update-status')->hourly();
        $schedule->command('orders:update-tracking-numbers')->dailyAt('01:00');
    }
}
//* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
//Cài đặt chạy cron job để chạy lệnh này mỗi phút