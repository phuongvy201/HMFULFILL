<?php

// Test script để kiểm tra việc tìm kiếm user_id trong ShippingOverride
require_once 'vendor/autoload.php';

use App\Models\ShippingOverride;
use App\Models\ShippingPrice;

// Giả lập dữ liệu test
$testData = [
    'shipping_price_id' => 1,
    'user_ids' => '[17]', // String JSON như trong database
    'override_price' => 15.99,
    'currency' => 'USD'
];

echo "=== Test ShippingOverride với user_ids = '[17]' ===\n";

// Test 1: Tìm với user_id = 17 (integer)
echo "Test 1: Tìm với user_id = 17 (integer)\n";
$result1 = ShippingOverride::where('shipping_price_id', 1)
    ->where(function ($query) {
        $query->whereJsonContains('user_ids', 17)
            ->orWhereJsonContains('user_ids', '17');
    })
    ->first();

if ($result1) {
    echo "✅ Tìm thấy với user_id = 17\n";
} else {
    echo "❌ Không tìm thấy với user_id = 17\n";
}

// Test 2: Tìm với user_id = '17' (string)
echo "\nTest 2: Tìm với user_id = '17' (string)\n";
$result2 = ShippingOverride::where('shipping_price_id', 1)
    ->where(function ($query) {
        $query->whereJsonContains('user_ids', 17)
            ->orWhereJsonContains('user_ids', '17');
    })
    ->first();

if ($result2) {
    echo "✅ Tìm thấy với user_id = '17'\n";
} else {
    echo "❌ Không tìm thấy với user_id = '17'\n";
}

// Test 3: Sử dụng hàm findForUser
echo "\nTest 3: Sử dụng hàm findForUser(1, 17)\n";
$result3 = ShippingOverride::findForUser(1, 17);

if ($result3) {
    echo "✅ findForUser tìm thấy override\n";
    echo "   - Override price: " . $result3->override_price . "\n";
    echo "   - Currency: " . $result3->currency . "\n";
} else {
    echo "❌ findForUser không tìm thấy override\n";
}

echo "\n=== Kết thúc test ===\n";
