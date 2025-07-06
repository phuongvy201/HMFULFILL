<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\UserTier;
use App\Services\UserTierService;
use Carbon\Carbon;

// Khởi tạo Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST USER TIER INTERFACE ===\n\n";

try {
    // Test 1: Kiểm tra UserTierService
    echo "1. Testing UserTierService...\n";
    $tierService = app(UserTierService::class);
    $stats = $tierService->getTierStatistics();

    echo "   - Total users: " . $stats['total_users'] . "\n";
    echo "   - Tier distribution:\n";
    foreach ($stats['tier_distribution'] as $tier => $data) {
        echo "     * {$tier}: {$data['user_count']} users ({$data['percentage']}%)\n";
    }
    echo "   - Effective month: " . $stats['effective_month'] . "\n\n";

    // Test 2: Kiểm tra User model với userTiers relationship
    echo "2. Testing User model with userTiers relationship...\n";
    $users = User::with(['userTiers' => function ($query) {
        $query->where('effective_month', Carbon::now()->startOfMonth());
    }])->take(5)->get();

    echo "   - Found " . $users->count() . " users\n";
    foreach ($users as $user) {
        $currentTier = $user->userTiers->first();
        echo "   - User {$user->id} ({$user->first_name} {$user->last_name}): ";
        if ($currentTier) {
            echo "Tier {$currentTier->tier} ({$currentTier->order_count} orders)\n";
        } else {
            echo "No tier data\n";
        }
    }
    echo "\n";

    // Test 3: Kiểm tra UserTier model methods
    echo "3. Testing UserTier model methods...\n";
    $testUserId = $users->first()->id ?? 1;

    // Test determineTier
    echo "   - Testing determineTier method:\n";
    $testCases = [0, 1000, 2000, 5000, 10000];
    foreach ($testCases as $orderCount) {
        $tier = UserTier::determineTier($orderCount);
        echo "     * {$orderCount} orders -> {$tier} tier\n";
    }

    // Test getCurrentTier
    echo "   - Testing getCurrentTier for user {$testUserId}:\n";
    $currentTier = UserTier::getCurrentTier($testUserId);
    if ($currentTier) {
        echo "     * Current tier: {$currentTier->tier} ({$currentTier->order_count} orders)\n";
    } else {
        echo "     * No current tier found\n";
    }
    echo "\n";

    // Test 4: Kiểm tra createOrUpdateTier
    echo "4. Testing createOrUpdateTier...\n";
    $testMonth = Carbon::now()->startOfMonth();
    $testTier = UserTier::createOrUpdateTier($testUserId, 'Gold', 5000, $testMonth);
    echo "   - Created/Updated tier: {$testTier->tier} ({$testTier->order_count} orders) for {$testTier->effective_month->format('Y-m')}\n\n";

    // Test 5: Kiểm tra thống kê sau khi cập nhật
    echo "5. Testing statistics after update...\n";
    $updatedStats = $tierService->getTierStatistics();
    echo "   - Updated total users: " . $updatedStats['total_users'] . "\n";
    echo "   - Gold tier users: " . $updatedStats['tier_distribution']['Gold']['user_count'] . "\n\n";

    echo "=== ALL TESTS PASSED ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
