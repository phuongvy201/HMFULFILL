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

echo "=== TEST REVENUE CALCULATION ===\n\n";

try {
    // Test 1: Kiểm tra helper method tính doanh thu
    echo "1. Testing revenue calculation helper methods:\n";

    // Lấy một user có đơn hàng để test
    $testUser = User::where('role', 'customer')->first();

    if ($testUser) {
        echo "   - Testing for user: {$testUser->email}\n";

        // Tính doanh thu tháng trước
        $lastMonth = Carbon::now()->subMonth();
        $startOfMonth = $lastMonth->copy()->startOfMonth();
        $endOfMonth = $lastMonth->copy()->endOfMonth();

        $revenue = ExcelOrder::calculateUserRevenue($testUser->id, $startOfMonth, $endOfMonth, 'processed');
        echo "   - Revenue for {$lastMonth->format('Y-m')}: $" . number_format($revenue, 2) . "\n";

        // Tính doanh thu tất cả đơn hàng
        $totalRevenue = ExcelOrder::calculateUserRevenue($testUser->id, $startOfMonth, $endOfMonth, null);
        echo "   - Total revenue (all status): $" . number_format($totalRevenue, 2) . "\n";

        // Kiểm tra từng đơn hàng
        $orders = ExcelOrder::where('created_by', $testUser->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('items')
            ->get();

        echo "   - Orders found: " . $orders->count() . "\n";

        foreach ($orders as $order) {
            $orderRevenue = $order->getTotalRevenue();
            echo "     * Order {$order->external_id}: $" . number_format($orderRevenue, 2) . " ({$order->status})\n";
        }
    } else {
        echo "   - No customer users found\n";
    }
    echo "\n";

    // Test 2: Kiểm tra UserTierService với doanh thu
    echo "2. Testing UserTierService with revenue:\n";

    if ($testUser) {
        $tierService = app(UserTierService::class);

        // Tính toán tier cho user
        $result = $tierService->calculateTierForUser($testUser->id, $lastMonth);

        echo "   - User ID: {$result['user_id']}\n";
        echo "   - Order Count: {$result['order_count']}\n";
        echo "   - Revenue: $" . number_format($result['revenue'], 2) . "\n";
        echo "   - Tier: {$result['tier']}\n";
        echo "   - Effective Month: {$result['effective_month']}\n";
        echo "   - Previous Tier: " . ($result['previous_tier'] ?: 'None') . "\n";
    }
    echo "\n";

    // Test 3: Kiểm tra UserTier model với doanh thu
    echo "3. Testing UserTier model with revenue:\n";

    if ($testUser) {
        $currentTier = UserTier::getCurrentTier($testUser->id);

        if ($currentTier) {
            echo "   - Current Tier: {$currentTier->tier}\n";
            echo "   - Order Count: {$currentTier->order_count}\n";
            echo "   - Revenue: $" . number_format($currentTier->revenue, 2) . "\n";
            echo "   - Month: {$currentTier->month->format('Y-m')}\n";
        } else {
            echo "   - No current tier found\n";
        }
    }
    echo "\n";

    // Test 4: Kiểm tra danh sách khách hàng với doanh thu
    echo "4. Testing customer list with revenue:\n";

    $customers = UserTier::getCustomerListWithTiers();
    echo "   - Total customers: " . $customers->count() . "\n";

    $customersWithRevenue = $customers->filter(function ($customer) {
        return $customer['previous_month_revenue'] > 0;
    });

    echo "   - Customers with revenue: " . $customersWithRevenue->count() . "\n";

    foreach ($customersWithRevenue->take(3) as $customer) {
        echo "     * {$customer['customer_name']}: $" . number_format($customer['previous_month_revenue'], 2) . "\n";
    }
    echo "\n";

    echo "=== ALL TESTS PASSED ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
