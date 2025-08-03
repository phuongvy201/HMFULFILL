<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\AutoUpdateOrderStatus;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected $commands = [
        \App\Console\Commands\AutoUpdateOrderStatus::class,
        \App\Console\Commands\UpdateTrackingNumbers::class,
        \App\Console\Commands\ScheduleTierCalculation::class,
        \App\Console\Commands\ReleaseOnHoldOrders::class,
    ];
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
        $schedule->command('orders:update-status')->everyMinute();
        $schedule->command('orders:update-tracking-numbers')->dailyAt('01:00');
        $schedule->command('orders:release-on-hold')->everyMinute();

        // Tính toán tier cho user vào ngày đầu tiên của mỗi tháng lúc 2:00 AM
        $schedule->command('users:schedule-tier-calculation')
            ->monthlyOn(1, '02:00')
            ->withoutOverlapping()
            ->runInBackground();
    }
}
//* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
//Cài đặt chạy cron job để chạy lệnh này mỗi phút