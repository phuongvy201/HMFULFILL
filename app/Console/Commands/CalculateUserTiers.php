<?php

namespace App\Console\Commands;

use App\Services\UserTierService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateUserTiers extends Command
{
    protected $signature = 'users:calculate-tiers {--month= : Tháng cần tính toán (format: YYYY-MM)} {--user-id= : Chỉ tính toán cho user cụ thể}';
    protected $description = 'Tính toán và cập nhật tier cho user dựa trên số đơn hàng tháng trước';

    public function handle()
    {
        $monthOption = $this->option('month');
        $userIdOption = $this->option('user-id');

        if ($userIdOption) {
            $this->calculateTierForSpecificUser($userIdOption, $monthOption);
        } else {
            $this->calculateTiersForAllUsers($monthOption);
        }
    }

    private function calculateTiersForAllUsers(?string $monthOption)
    {
        $month = $monthOption ? Carbon::createFromFormat('Y-m', $monthOption) : Carbon::now()->subMonth();

        $this->info("🔄 Bắt đầu tính toán tier cho tất cả user dựa trên đơn hàng tháng {$month->format('Y-m')}...");

        $tierService = app(UserTierService::class);
        $results = $tierService->calculateAndUpdateTiers($month);

        $this->info("✅ Hoàn tất tính toán tier!");
        $this->info("📊 Thống kê:");
        $this->info("   - Tổng số user được xử lý: {$results['total_users']}");
        $this->info("   - Số user cập nhật thành công: " . count($results['updated_tiers']));
        $this->info("   - Số lỗi: " . count($results['errors']));

        // Hiển thị chi tiết các tier được cập nhật
        if (!empty($results['updated_tiers'])) {
            $this->info("\n📋 Chi tiết tier được cập nhật:");
            $tierCounts = [];

            foreach ($results['updated_tiers'] as $result) {
                $tier = $result['tier'];
                $tierCounts[$tier] = ($tierCounts[$tier] ?? 0) + 1;

                $changeIndicator = '';
                if ($result['previous_tier'] && $result['previous_tier'] !== $tier) {
                    $changeIndicator = " (↑ từ {$result['previous_tier']})";
                }

                $this->line("   - User {$result['user_id']}: {$tier} ({$result['order_count']} đơn, $" . number_format($result['revenue'], 2) . "){$changeIndicator}");
            }

            $this->info("\n🏆 Phân bố tier:");
            foreach ($tierCounts as $tier => $count) {
                $percentage = round(($count / count($results['updated_tiers'])) * 100, 1);
                $this->info("   - {$tier}: {$count} user ({$percentage}%)");
            }
        }

        // Hiển thị lỗi nếu có
        if (!empty($results['errors'])) {
            $this->error("\n❌ Các lỗi xảy ra:");
            foreach ($results['errors'] as $error) {
                $this->error("   - User {$error['user_id']}: {$error['error']}");
            }
        }
    }

    private function calculateTierForSpecificUser(int $userId, ?string $monthOption)
    {
        $month = $monthOption ? Carbon::createFromFormat('Y-m', $monthOption) : Carbon::now()->subMonth();

        $this->info("🔄 Tính toán tier cho user {$userId} dựa trên đơn hàng tháng {$month->format('Y-m')}...");

        $tierService = app(UserTierService::class);

        try {
            $result = $tierService->calculateTierForUser($userId, $month);

            $this->info("✅ Hoàn tất tính toán tier cho user {$userId}!");
            $this->info("📊 Kết quả:");
            $this->info("   - Số đơn hàng: {$result['order_count']}");
            $this->info("   - Tier: {$result['tier']}");
            $this->info("   - Tháng có hiệu lực: {$result['effective_month']}");

            if ($result['previous_tier']) {
                if ($result['previous_tier'] !== $result['tier']) {
                    $this->info("   - Thay đổi: ↑ từ {$result['previous_tier']} lên {$result['tier']}");
                } else {
                    $this->info("   - Thay đổi: Giữ nguyên tier {$result['tier']}");
                }
            } else {
                $this->info("   - Thay đổi: Tier mới");
            }
        } catch (\Exception $e) {
            $this->error("❌ Lỗi khi tính toán tier cho user {$userId}: " . $e->getMessage());
            Log::error("Lỗi khi tính toán tier cho user {$userId}: " . $e->getMessage());
        }
    }
}
