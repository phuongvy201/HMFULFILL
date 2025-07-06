<?php

/**
 * Test script cho tính toán tier theo tháng cụ thể
 * Chạy: php test_tier_month.php
 */

require_once 'vendor/autoload.php';

use App\Services\UserTierService;
use App\Models\UserTier;
use App\Models\ExcelOrder;
use Carbon\Carbon;

// Khởi tạo Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test tính toán tier theo tháng cụ thể...\n\n";

try {
    $tierService = app(UserTierService::class);

    // Test 1: Kiểm tra tháng hiện tại
    $currentMonth = Carbon::now();
    echo "📅 Tháng hiện tại: " . $currentMonth->format('Y-m') . "\n";

    // Test 2: Kiểm tra tháng trước
    $lastMonth = Carbon::now()->subMonth();
    echo "📅 Tháng trước: " . $lastMonth->format('Y-m') . "\n";

    // Test 3: Kiểm tra tháng 6 (nếu đang ở tháng 7)
    $june2024 = Carbon::createFromFormat('Y-m', '2024-06');
    echo "📅 Tháng 6/2024: " . $june2024->format('Y-m') . "\n\n";

    // Test 4: Kiểm tra dữ liệu đơn hàng theo tháng
    $usersWithOrders = ExcelOrder::distinct()->pluck('created_by')->filter();

    if ($usersWithOrders->count() > 0) {
        $sampleUserId = $usersWithOrders->first();
        echo "👤 Sample user ID: {$sampleUserId}\n\n";

        // Test 5: Đếm đơn hàng theo tháng
        $months = [
            'Tháng hiện tại' => $currentMonth,
            'Tháng trước' => $lastMonth,
            'Tháng 6/2024' => $june2024
        ];

        foreach ($months as $label => $month) {
            $orderCount = ExcelOrder::where('created_by', $sampleUserId)
                ->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth()
                ])
                ->count();

            echo "📦 {$label} ({$month->format('Y-m')}): {$orderCount} đơn hàng\n";
        }
        echo "\n";

        // Test 6: Tính toán tier cho tháng 6
        echo "🎯 Test tính toán tier cho tháng 6/2024:\n";
        try {
            $result = $tierService->calculateTierForUser($sampleUserId, $june2024);
            echo "✅ Kết quả: {$result['tier']} ({$result['order_count']} đơn)\n";
            echo "📅 Tháng có hiệu lực: {$result['effective_month']}\n";
            echo "🔄 Tier trước: " . ($result['previous_tier'] ?: 'Không có') . "\n\n";
        } catch (Exception $e) {
            echo "❌ Lỗi: " . $e->getMessage() . "\n\n";
        }

        // Test 7: Tính toán tier cho tháng trước
        echo "🎯 Test tính toán tier cho tháng trước:\n";
        try {
            $result = $tierService->calculateTierForUser($sampleUserId, $lastMonth);
            echo "✅ Kết quả: {$result['tier']} ({$result['order_count']} đơn)\n";
            echo "📅 Tháng có hiệu lực: {$result['effective_month']}\n";
            echo "🔄 Tier trước: " . ($result['previous_tier'] ?: 'Không có') . "\n\n";
        } catch (Exception $e) {
            echo "❌ Lỗi: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "❌ Không có user nào có đơn hàng để test\n\n";
    }

    // Test 8: Thống kê tier hiện tại
    echo "📈 Thống kê tier hiện tại:\n";
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
    echo "\n💡 Hướng dẫn sử dụng:\n";
    echo "php artisan users:calculate-tiers --month=2024-06  # Tính tier cho tháng 6\n";
    echo "php artisan users:calculate-tiers --month=2024-05  # Tính tier cho tháng 5\n";
    echo "php artisan users:calculate-tiers                  # Tính tier cho tháng trước\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
