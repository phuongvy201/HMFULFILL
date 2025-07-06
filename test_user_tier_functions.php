<?php

require_once 'vendor/autoload.php';

use App\Models\UserTier;
use App\Models\User;
use Carbon\Carbon;

// Test các hàm UserTier

echo "=== Test UserTier Functions ===\n\n";

// Test 1: Lấy danh sách khách hàng với tier hiện tại
echo "1. Test getCustomersWithCurrentTier():\n";
try {
    $customers = UserTier::getCustomersWithCurrentTier();
    echo "   - Tổng số khách hàng: " . $customers->count() . "\n";

    if ($customers->count() > 0) {
        $firstCustomer = $customers->first();
        echo "   - Khách hàng đầu tiên:\n";
        echo "     + ID: " . $firstCustomer['id'] . "\n";
        echo "     + Tên: " . $firstCustomer['name'] . "\n";
        echo "     + Email: " . $firstCustomer['email'] . "\n";
        echo "     + Tier hiện tại: " . $firstCustomer['current_tier'] . "\n";
        echo "     + Số đơn hàng hiện tại: " . $firstCustomer['current_order_count'] . "\n";
        echo "     + Số đơn hàng tháng trước: " . $firstCustomer['previous_month_order_count'] . "\n";
        echo "     + Tháng áp dụng: " . ($firstCustomer['effective_month'] ?: 'Chưa có') . "\n";
        echo "     + Ngày cập nhật: " . ($firstCustomer['updated_at'] ?: 'Chưa cập nhật') . "\n";
    }
} catch (Exception $e) {
    echo "   - Lỗi: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Lấy thống kê tổng quan
echo "2. Test getTierOverview():\n";
try {
    $overview = UserTier::getTierOverview();
    echo "   - Tổng khách hàng: " . $overview['total_customers'] . "\n";
    echo "   - Khách hàng có tier: " . $overview['customers_with_tier'] . "\n";
    echo "   - Khách hàng chưa có tier: " . $overview['customers_without_tier'] . "\n";
    echo "   - Tổng đơn hàng: " . $overview['total_orders'] . "\n";
    echo "   - Trung bình đơn hàng/khách: " . $overview['average_orders_per_customer'] . "\n";

    echo "   - Phân bố tier:\n";
    foreach ($overview['tier_distribution'] as $tier => $data) {
        echo "     + $tier: " . $data['count'] . " khách hàng (" . $data['percentage'] . "%) - " . $data['total_orders'] . " đơn hàng\n";
    }
} catch (Exception $e) {
    echo "   - Lỗi: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Format tier badge
echo "3. Test formatTierBadge():\n";
$tiers = ['Diamond', 'Gold', 'Silver', 'Wood', null];
foreach ($tiers as $tier) {
    $badge = UserTier::formatTierBadge($tier);
    echo "   - Tier '$tier': $badge\n";
}

echo "\n";

// Test 4: Lấy màu sắc tier
echo "4. Test getTierColor():\n";
foreach ($tiers as $tier) {
    if ($tier) {
        $color = UserTier::getTierColor($tier);
        echo "   - Tier '$tier': $color\n";
    }
}

echo "\n";

// Test 5: Tìm kiếm và lọc
echo "5. Test tìm kiếm và lọc:\n";
try {
    // Tìm kiếm theo tier
    $diamondCustomers = UserTier::getCustomersWithCurrentTier(null, 'Diamond');
    echo "   - Khách hàng Diamond: " . $diamondCustomers->count() . "\n";

    // Tìm kiếm theo từ khóa
    $searchCustomers = UserTier::getCustomersWithCurrentTier('test');
    echo "   - Khách hàng có từ 'test': " . $searchCustomers->count() . "\n";
} catch (Exception $e) {
    echo "   - Lỗi: " . $e->getMessage() . "\n";
}

echo "\n=== Test hoàn thành ===\n";
