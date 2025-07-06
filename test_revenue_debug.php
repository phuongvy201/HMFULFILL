<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\ExcelOrder;
use App\Models\UserTier;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG REVENUE CALCULATION ===\n\n";

try {
    // Lấy một user để test
    $testUser = User::where('role', 'customer')->first();

    if (!$testUser) {
        echo "❌ Không có user customer nào\n";
        exit;
    }

    echo "👤 Testing user: {$testUser->email} (ID: {$testUser->id})\n\n";

    // Kiểm tra tháng trước
    $previousMonth = Carbon::now()->subMonth();
    $startOfMonth = $previousMonth->copy()->startOfMonth();
    $endOfMonth = $previousMonth->copy()->endOfMonth();

    echo "📅 Checking period: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n\n";

    // Kiểm tra đơn hàng theo created_by
    echo "🔍 Checking orders by created_by:\n";
    $ordersByCreatedBy = ExcelOrder::where('created_by', $testUser->id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get();

    echo "   - Total orders found: " . $ordersByCreatedBy->count() . "\n";

    foreach ($ordersByCreatedBy as $order) {
        $orderRevenue = $order->getTotalRevenue();
        echo "     * Order {$order->external_id}: $" . number_format($orderRevenue, 2) . " ({$order->status})\n";
    }

    // Tính doanh thu từ đơn hàng đã thực hiện
    $processedRevenue = ExcelOrder::where('created_by', $testUser->id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->where('status', 'processed')
        ->with('items')
        ->get()
        ->sum(function ($order) {
            return $order->getTotalRevenue();
        });

    echo "\n💰 Processed orders revenue: $" . number_format($processedRevenue, 2) . "\n";

    // Kiểm tra đơn hàng theo buyer_email
    echo "\n🔍 Checking orders by buyer_email:\n";
    $ordersByEmail = ExcelOrder::where('buyer_email', $testUser->email)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get();

    echo "   - Total orders found: " . $ordersByEmail->count() . "\n";

    foreach ($ordersByEmail as $order) {
        $orderRevenue = $order->getTotalRevenue();
        echo "     * Order {$order->external_id}: $" . number_format($orderRevenue, 2) . " ({$order->status})\n";
    }

    // So sánh kết quả
    echo "\n📊 Comparison:\n";
    echo "   - Orders by created_by: " . $ordersByCreatedBy->count() . "\n";
    echo "   - Orders by buyer_email: " . $ordersByEmail->count() . "\n";
    echo "   - Revenue by created_by (processed): $" . number_format($processedRevenue, 2) . "\n";

    // Test UserTier method
    echo "\n🏆 Testing UserTier method:\n";
    $customers = UserTier::getCustomerListWithTiers();
    $testCustomer = $customers->where('id', $testUser->id)->first();

    if ($testCustomer) {
        echo "   - Previous month revenue: $" . number_format($testCustomer['previous_month_revenue'], 2) . "\n";
        echo "   - Previous month order count: " . $testCustomer['previous_month_order_count'] . "\n";
    } else {
        echo "   - Customer not found in tier list\n";
    }

    echo "\n✅ Debug completed!\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
