<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\ExcelOrder;
use App\Models\UserTier;
use App\Services\UserTierService;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST CUSTOMER TIER FUNCTIONALITY ===\n\n";

try {
    // Test 1: Kiểm tra user customer
    echo "1. Testing customer user:\n";

    $customer = User::where('role', 'customer')->first();

    if (!$customer) {
        echo "❌ Không có user customer nào\n";
        exit;
    }

    echo "✅ Found customer: {$customer->email} (ID: {$customer->id})\n\n";

    // Test 2: Kiểm tra UserTierService
    echo "2. Testing UserTierService:\n";

    $tierService = new UserTierService();
    $tierInfo = $tierService->getUserTierInfo($customer->id);

    echo "   - Current Tier: {$tierInfo['current_tier']}\n";
    echo "   - This Month Orders: {$tierInfo['this_month_orders']}\n";

    if ($tierInfo['next_tier_threshold']) {
        echo "   - Next Tier: {$tierInfo['next_tier_threshold']['tier']}\n";
        echo "   - Orders Needed: {$tierInfo['next_tier_threshold']['orders_needed']}\n";
        echo "   - Threshold: {$tierInfo['next_tier_threshold']['threshold']}\n";
    } else {
        echo "   - Next Tier: Max Tier (Diamond)\n";
    }

    echo "\n";

    // Test 3: Kiểm tra đơn hàng tháng hiện tại
    echo "3. Testing current month orders:\n";

    $currentMonth = Carbon::now()->startOfMonth();
    $startOfMonth = $currentMonth->copy()->startOfMonth();
    $endOfMonth = $currentMonth->copy()->endOfMonth();

    echo "   - Checking period: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n";

    $orders = ExcelOrder::where('created_by', $customer->id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get();

    echo "   - Total orders found: {$orders->count()}\n";

    if ($orders->count() > 0) {
        echo "   - Order details:\n";
        foreach ($orders->take(5) as $order) {
            echo "     * Order ID: {$order->id}, Created: {$order->created_at->format('Y-m-d H:i')}\n";
        }
    }

    echo "\n";

    // Test 4: Kiểm tra UserTier model
    echo "4. Testing UserTier model:\n";

    $currentTier = UserTier::getCurrentTier($customer->id);

    if ($currentTier) {
        echo "   - Current Tier Record: {$currentTier->tier}\n";
        echo "   - Month: {$currentTier->month->format('Y-m')}\n";
        echo "   - Order Count: {$currentTier->order_count}\n";
        echo "   - Revenue: $" . number_format($currentTier->revenue, 2) . "\n";
    } else {
        echo "   - No tier record found\n";
    }

    echo "\n";

    // Test 5: Kiểm tra tier history
    echo "5. Testing tier history:\n";

    $tierHistory = UserTier::where('user_id', $customer->id)
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get();

    echo "   - History records found: {$tierHistory->count()}\n";

    foreach ($tierHistory as $history) {
        echo "   - {$history->month->format('Y-m')}: {$history->tier} ({$history->order_count} orders, $" . number_format($history->revenue, 2) . ")\n";
    }

    echo "\n✅ All tests completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
