<?php

namespace App\Jobs;

use App\Services\UserTierService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateUserTiersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->subMonth();
    }

    /**
     * Execute the job.
     */
    public function handle(UserTierService $tierService): void
    {
        Log::info('Bắt đầu job tính toán tier tự động', [
            'month' => $this->month->format('Y-m'),
            'job_id' => $this->job->getJobId()
        ]);

        try {
            $results = $tierService->calculateAndUpdateTiers($this->month);

            Log::info('Hoàn tất job tính toán tier tự động', [
                'month' => $this->month->format('Y-m'),
                'total_users' => $results['total_users'],
                'success_count' => count($results['updated_tiers']),
                'error_count' => count($results['errors'])
            ]);

            // Gửi thông báo cho admin nếu có lỗi
            if (!empty($results['errors'])) {
                Log::warning('Có lỗi xảy ra khi tính toán tier tự động', [
                    'errors' => $results['errors']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi chạy job tính toán tier tự động: ' . $e->getMessage(), [
                'month' => $this->month->format('Y-m'),
                'exception' => $e
            ]);

            throw $e; // Re-throw để job có thể retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job tính toán tier tự động thất bại', [
            'month' => $this->month->format('Y-m'),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
