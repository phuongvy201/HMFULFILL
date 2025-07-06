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

echo "=== DEBUG TIER ORDERS ISSUE ===\n\n";

try {
    // Test 1: Kiểm tra thời gian hiện tại
    echo "1. Current time check:\n";
    echo "   - Carbon::now(): " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
    echo "   - Carbon::now()->startOfMonth(): " . Carbon::now()->startOfMonth()->format('Y-m-d H:i:s') . "\n";
    echo "   - Carbon::now()->endOfMonth(): " . Carbon::now()->endOfMonth()->format('Y-m-d H:i:s') . "\n\n";

    // Test 2: Kiểm tra user customer
    $customer = User::where('role', 'customer')->first();
    if (!$customer) {
        echo "❌ Không có user customer nào\n";
        exit;
    }
    echo "2. Customer: {$customer->email} (ID: {$customer->id})\n\n";

    // Test 3: Kiểm tra UserTierService
    echo "3. UserTierService test:\n";
    $tierService = new UserTierService();
    $tierInfo = $tierService->getUserTierInfo($customer->id);

    echo "   - Current Tier: {$tierInfo['current_tier']}\n";
    echo "   - This Month Orders: {$tierInfo['this_month_orders']}\n";
    echo "   - Current Tier Data: " . ($tierInfo['current_tier_data'] ? 'Yes' : 'No') . "\n\n";

    // Test 4: Kiểm tra đơn hàng theo tháng hiện tại
    echo "4. Current month orders check:\n";
    $currentMonth = Carbon::now()->startOfMonth();
    $startOfMonth = $currentMonth->copy()->startOfMonth();
    $endOfMonth = $currentMonth->copy()->endOfMonth();

    echo "   - Checking period: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n";

    $currentMonthOrders = ExcelOrder::where('created_by', $customer->id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get();

    echo "   - Total orders found: {$currentMonthOrders->count()}\n";

    if ($currentMonthOrders->count() > 0) {
        echo "   - Order details:\n";
        foreach ($currentMonthOrders->take(5) as $order) {
            echo "     * Order ID: {$order->id}, External ID: {$order->external_id}, Created: {$order->created_at->format('Y-m-d H:i')}\n";
        }
    }

    echo "\n";

    // Test 5: Kiểm tra đơn hàng theo tháng trước
    echo "5. Previous month orders check:\n";
    $previousMonth = Carbon::now()->subMonth()->startOfMonth();
    $prevStartOfMonth = $previousMonth->copy()->startOfMonth();
    $prevEndOfMonth = $previousMonth->copy()->endOfMonth();

    echo "   - Checking period: {$prevStartOfMonth->format('Y-m-d')} to {$prevEndOfMonth->format('Y-m-d')}\n";

    $previousMonthOrders = ExcelOrder::where('created_by', $customer->id)
        ->whereBetween('created_at', [$prevStartOfMonth, $prevEndOfMonth])
        ->get();

    echo "   - Total orders found: {$previousMonthOrders->count()}\n";

    if ($previousMonthOrders->count() > 0) {
        echo "   - Order details:\n";
        foreach ($previousMonthOrders->take(5) as $order) {
            echo "     * Order ID: {$order->id}, External ID: {$order->external_id}, Created: {$order->created_at->format('Y-m-d H:i')}\n";
        }
    }

    echo "\n";

    // Test 6: Kiểm tra UserTier records
    echo "6. UserTier records check:\n";
    $userTiers = UserTier::where('user_id', $customer->id)
        ->orderBy('month', 'desc')
        ->get();

    echo "   - Total tier records: {$userTiers->count()}\n";

    foreach ($userTiers as $tier) {
        echo "   - {$tier->month->format('Y-m')}: {$tier->tier} ({$tier->order_count} orders, $" . number_format($tier->revenue, 2) . ")\n";
    }

    echo "\n";

    // Test 7: Kiểm tra tất cả đơn hàng của user
    echo "7. All user orders check:\n";
    $allOrders = ExcelOrder::where('created_by', $customer->id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    echo "   - Total orders (all time): {$allOrders->count()}\n";

    if ($allOrders->count() > 0) {
        echo "   - Recent orders:\n";
        foreach ($allOrders as $order) {
            echo "     * {$order->created_at->format('Y-m-d H:i')}: {$order->external_id} ({$order->status})\n";
        }
    }

    echo "\n✅ Debug completed!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
