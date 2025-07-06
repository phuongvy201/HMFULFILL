<?php

/**
 * Test script cho UserTierController
 * Chạy: php test_user_tier_controller.php
 */

require_once 'vendor/autoload.php';

use App\Http\Controllers\Admin\UserTierController;
use App\Services\UserTierService;
use App\Models\User;
use App\Models\UserTier;
use App\Models\ExcelOrder;
use Carbon\Carbon;

// Khởi tạo Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test UserTierController...\n\n";

try {
    $tierService = app(UserTierService::class);
    $controller = app(UserTierController::class);

    // Test 1: Kiểm tra dữ liệu user
    echo "📊 Test 1: Kiểm tra dữ liệu user\n";
    $totalUsers = User::count();
    echo "Tổng số user: {$totalUsers}\n";

    $usersWithTiers = UserTier::distinct('user_id')->count();
    echo "Số user có tier: {$usersWithTiers}\n\n";

    // Test 2: Kiểm tra dữ liệu đơn hàng
    echo "📦 Test 2: Kiểm tra dữ liệu đơn hàng\n";
    $totalOrders = ExcelOrder::count();
    echo "Tổng số đơn hàng: {$totalOrders}\n";

    $usersWithOrders = ExcelOrder::distinct()->pluck('created_by')->filter();
    echo "Số user có đơn hàng: " . $usersWithOrders->count() . "\n\n";

    // Test 3: Test hàm index (giả lập request)
    echo "🎯 Test 3: Test hàm index\n";

    // Tạo mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([]); // Không có filter

    // Lấy kết quả từ controller
    $response = $controller->index($request);

    if ($response instanceof \Illuminate\View\View) {
        $users = $response->getData()['users'];
        echo "✅ Hàm index hoạt động bình thường\n";
        echo "Số user được trả về: " . $users->count() . "\n";

        if ($users->count() > 0) {
            $firstUser = $users->first();
            echo "User đầu tiên: {$firstUser->name}\n";

            if (isset($firstUser->tier_info)) {
                echo "Tier hiện tại: " . $firstUser->tier_info['current_tier'] . "\n";
                echo "Số đơn hàng tháng trước: " . $firstUser->tier_info['last_month_order_count'] . "\n";
                echo "Tháng áp dụng: " . ($firstUser->tier_info['effective_month'] ?: 'N/A') . "\n";
                echo "Ngày cập nhật: " . ($firstUser->tier_info['updated_at'] ? $firstUser->tier_info['updated_at']->format('d/m/Y H:i') : 'N/A') . "\n";
            }
        }
    } else {
        echo "❌ Hàm index không trả về view\n";
    }
    echo "\n";

    // Test 4: Test với filter
    echo "🔍 Test 4: Test với filter\n";

    // Test filter theo tier
    $requestWithTier = new \Illuminate\Http\Request();
    $requestWithTier->merge(['tier' => 'Gold']);

    $responseWithTier = $controller->index($requestWithTier);
    if ($responseWithTier instanceof \Illuminate\View\View) {
        $filteredUsers = $responseWithTier->getData()['users'];
        echo "✅ Filter theo tier hoạt động\n";
        echo "Số user Gold tier: " . $filteredUsers->count() . "\n";
    }

    // Test filter theo search
    $requestWithSearch = new \Illuminate\Http\Request();
    $requestWithSearch->merge(['search' => 'test']);

    $responseWithSearch = $controller->index($requestWithSearch);
    if ($responseWithSearch instanceof \Illuminate\View\View) {
        $searchedUsers = $responseWithSearch->getData()['users'];
        echo "✅ Filter theo search hoạt động\n";
        echo "Số user tìm được: " . $searchedUsers->count() . "\n";
    }
    echo "\n";

    // Test 5: Test thống kê
    echo "📈 Test 5: Test thống kê tier\n";
    $stats = $tierService->getTierStatistics();
    echo "Tổng số user có tier: " . $stats['total_users'] . "\n";
    echo "Tháng hiệu lực: " . $stats['effective_month'] . "\n";

    if (!empty($stats['tier_distribution'])) {
        foreach ($stats['tier_distribution'] as $tier => $data) {
            echo "  {$tier}: {$data['user_count']} user ({$data['percentage']}%)\n";
        }
    }
    echo "\n";

    // Test 6: Test với user cụ thể
    if ($usersWithOrders->count() > 0) {
        $sampleUserId = $usersWithOrders->first();
        echo "👤 Test 6: Test với user {$sampleUserId}\n";

        $sampleUser = User::find($sampleUserId);
        if ($sampleUser) {
            $tierInfo = $tierService->getUserTierInfo($sampleUserId);
            echo "Tier hiện tại: " . $tierInfo['current_tier'] . "\n";
            echo "Số đơn hàng hiện tại: " . $tierInfo['this_month_orders'] . "\n";
            echo "Tier dự kiến: " . $tierInfo['projected_tier'] . "\n";

            if ($tierInfo['next_tier_threshold']) {
                echo "Cần thêm " . $tierInfo['next_tier_threshold']['orders_needed'] .
                    " đơn để lên " . $tierInfo['next_tier_threshold']['tier'] . "\n";
            }
        }
        echo "\n";
    }

    echo "✅ Tất cả test hoàn thành!\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
