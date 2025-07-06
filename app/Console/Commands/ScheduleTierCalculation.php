<?php

namespace App\Console\Commands;

use App\Jobs\CalculateUserTiersJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduleTierCalculation extends Command
{
    protected $signature = 'users:schedule-tier-calculation';
    protected $description = 'Schedule job tính toán tier cho user vào đầu tháng';

    public function handle()
    {
        $this->info('🔄 Đang schedule job tính toán tier...');

        // Dispatch job để tính toán tier cho tháng trước
        $previousMonth = Carbon::now()->subMonth();
        CalculateUserTiersJob::dispatch($previousMonth);

        $this->info("✅ Đã schedule job tính toán tier cho tháng {$previousMonth->format('Y-m')}");
        $this->info('📅 Job sẽ được xử lý trong queue system');
    }
}
