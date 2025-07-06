<?php

/**
 * Test script cho hệ thống Tier
 * Chạy: php test_tier_system.php
 */

require_once 'vendor/autoload.php';

use App\Services\UserTierService;
use App\Models\UserTier;
use App\Models\ExcelOrder;
use Carbon\Carbon;

// Khởi tạo Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Bắt đầu test hệ thống Tier...\n\n";

try {
    $tierService = app(UserTierService::class);

    // Test 1: Kiểm tra các tier thresholds
    echo "📊 Test 1: Kiểm tra tier thresholds\n";
    echo "Diamond (≥9000): " . UserTier::determineTier(9500) . "\n";
    echo "Gold (≥4500): " . UserTier::determineTier(5000) . "\n";
    echo "Silver (≥1500): " . UserTier::determineTier(2000) . "\n";
    echo "Wood (<1500): " . UserTier::determineTier(1000) . "\n\n";

    // Test 2: Kiểm tra dữ liệu đơn hàng
    echo "📦 Test 2: Kiểm tra dữ liệu đơn hàng\n";
    $totalOrders = ExcelOrder::count();
    echo "Tổng số đơn hàng: {$totalOrders}\n";

    $usersWithOrders = ExcelOrder::distinct()->pluck('created_by')->filter();
    echo "Số user có đơn hàng: " . $usersWithOrders->count() . "\n";

    if ($usersWithOrders->count() > 0) {
        $sampleUserId = $usersWithOrders->first();
        echo "Sample user ID: {$sampleUserId}\n";

        // Test 3: Kiểm tra đơn hàng của user sample
        $userOrders = ExcelOrder::where('created_by', $sampleUserId)->count();
        echo "Số đơn hàng của user {$sampleUserId}: {$userOrders}\n";

        // Test 4: Kiểm tra đơn hàng tháng trước
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthOrders = ExcelOrder::where('created_by', $sampleUserId)
            ->whereBetween('created_at', [
                $lastMonth->copy()->startOfMonth(),
                $lastMonth->copy()->endOfMonth()
            ])
            ->count();
        echo "Số đơn hàng tháng {$lastMonth->format('Y-m')}: {$lastMonthOrders}\n\n";

        // Test 5: Tính toán tier cho user sample
        echo "🎯 Test 5: Tính toán tier cho user {$sampleUserId}\n";
        $tierInfo = $tierService->getUserTierInfo($sampleUserId);
        echo "Tier hiện tại: " . $tierInfo['current_tier'] . "\n";
        echo "Số đơn hàng hiện tại: " . $tierInfo['this_month_orders'] . "\n";
        echo "Tier dự kiến: " . $tierInfo['projected_tier'] . "\n";

        if ($tierInfo['next_tier_threshold']) {
            echo "Cần thêm " . $tierInfo['next_tier_threshold']['orders_needed'] .
                " đơn để lên " . $tierInfo['next_tier_threshold']['tier'] . "\n";
        }
        echo "\n";

        // Test 6: Tính toán tier cho tháng trước
        echo "🔄 Test 6: Tính toán tier cho tháng {$lastMonth->format('Y-m')}\n";
        $result = $tierService->calculateTierForUser($sampleUserId, $lastMonth);
        echo "Kết quả: " . $result['tier'] . " ({$result['order_count']} đơn)\n";
        echo "Tháng có hiệu lực: " . $result['effective_month'] . "\n\n";
    } else {
        echo "❌ Không có user nào có đơn hàng để test\n\n";
    }

    // Test 7: Thống kê tier
    echo "📈 Test 7: Thống kê tier\n";
    $stats = $tierService->getTierStatistics();
    echo "Tổng số user có tier: " . $stats['total_users'] . "\n";
    echo "Tháng hiệu lực: " . $stats['effective_month'] . "\n";

    if (!empty($stats['tier_distribution'])) {
        foreach ($stats['tier_distribution'] as $tier => $data) {
            echo "  {$tier}: {$data['user_count']} user ({$data['percentage']}%)\n";
        }
    }
    echo "\n";

    echo "✅ Test hoàn tất!\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
