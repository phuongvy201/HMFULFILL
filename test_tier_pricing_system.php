<?php

/**
 * Test script cho hệ thống giá theo Tier
 * Chạy: php test_tier_pricing_system.php
 */

require_once 'vendor/autoload.php';

use App\Models\VariantTierPrice;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;
use App\Models\UserTier;
use App\Services\VariantTierPriceService;
use Carbon\Carbon;

// Khởi tạo Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Bắt đầu test hệ thống giá theo Tier...\n\n";

try {
    $tierPriceService = app(VariantTierPriceService::class);

    // Test 1: Kiểm tra tỷ lệ giảm giá theo tier
    echo "📊 Test 1: Kiểm tra tỷ lệ giảm giá theo tier\n";
    echo "Diamond: " . (VariantTierPrice::getDiscountRateForTier('Diamond') * 100) . "%\n";
    echo "Gold: " . (VariantTierPrice::getDiscountRateForTier('Gold') * 100) . "%\n";
    echo "Silver: " . (VariantTierPrice::getDiscountRateForTier('Silver') * 100) . "%\n";
    echo "Wood: " . (VariantTierPrice::getDiscountRateForTier('Wood') * 100) . "%\n\n";

    // Test 2: Kiểm tra dữ liệu variant và shipping prices
    echo "📦 Test 2: Kiểm tra dữ liệu variant và shipping prices\n";
    $totalVariants = ProductVariant::count();
    echo "Tổng số variant: {$totalVariants}\n";

    $variantsWithShippingPrices = ProductVariant::whereHas('shippingPrices')->count();
    echo "Variant có shipping prices: {$variantsWithShippingPrices}\n";

    if ($variantsWithShippingPrices > 0) {
        $sampleVariant = ProductVariant::with('shippingPrices')->first();
        echo "Sample variant ID: {$sampleVariant->id}\n";
        echo "Số shipping prices: " . $sampleVariant->shippingPrices->count() . "\n";

        // Test 3: Tạo giá theo tier cho variant sample
        echo "\n💰 Test 3: Tạo giá theo tier cho variant sample\n";
        $result = $tierPriceService->generateTierPricesFromShippingPrices();

        if ($result['success']) {
            echo "✅ Tạo thành công {$result['created_count']} giá theo tier\n";
            echo "Cập nhật {$result['updated_count']} giá theo tier\n";

            if (!empty($result['errors'])) {
                echo "⚠️ Có " . count($result['errors']) . " lỗi\n";
            }
        } else {
            echo "❌ Lỗi: {$result['error']}\n";
        }

        // Test 4: Kiểm tra giá theo tier đã tạo
        echo "\n🔍 Test 4: Kiểm tra giá theo tier đã tạo\n";
        $tierPrices = VariantTierPrice::where('variant_id', $sampleVariant->id)->get();
        echo "Số giá theo tier cho variant {$sampleVariant->id}: {$tierPrices->count()}\n";

        foreach ($tierPrices as $tierPrice) {
            echo "  - {$tierPrice->tier} ({$tierPrice->method}): \${$tierPrice->price} {$tierPrice->currency}\n";
        }

        // Test 5: Kiểm tra lấy giá theo user
        echo "\n👤 Test 5: Kiểm tra lấy giá theo user\n";
        $users = UserTier::with('user')->get();

        if ($users->count() > 0) {
            $sampleUser = $users->first();
            echo "Sample user ID: {$sampleUser->user_id}, Tier: {$sampleUser->tier}\n";

            $userPrice = VariantTierPrice::getPriceForUser(
                $sampleVariant->id,
                $sampleUser->user_id,
                'seller_1st'
            );

            if ($userPrice) {
                echo "Giá cho user: \${$userPrice->price} {$userPrice->currency} (Tier: {$userPrice->tier})\n";
            } else {
                echo "Không tìm thấy giá cho user\n";
            }
        } else {
            echo "Không có user tier nào\n";
        }

        // Test 6: Thống kê giá theo tier
        echo "\n📈 Test 6: Thống kê giá theo tier\n";
        $stats = $tierPriceService->getTierPriceStatistics();

        echo "Tổng số variant: {$stats['total_variants']}\n";
        echo "Variant có giá theo tier: {$stats['variants_with_tier_prices']}\n";
        echo "Tỷ lệ bao phủ: {$stats['coverage_percentage']}%\n";

        if (!empty($stats['tier_statistics'])) {
            echo "\nThống kê theo tier:\n";
            foreach ($stats['tier_statistics'] as $tier => $methods) {
                echo "  {$tier}:\n";
                foreach ($methods as $method => $data) {
                    echo "    {$method}: {$data->variant_count} variants, \${$data->avg_price} TB\n";
                }
            }
        }

        // Test 7: Kiểm tra variant chưa có giá theo tier
        echo "\n🔍 Test 7: Kiểm tra variant chưa có giá theo tier\n";
        $variantsWithoutTierPrices = $tierPriceService->getVariantsWithoutTierPrices();
        echo "Variant chưa có giá theo tier: {$variantsWithoutTierPrices['count']}\n";

        // Test 8: Test ProductVariant methods
        echo "\n🔄 Test 8: Test ProductVariant methods\n";

        // Test getFirstItemPrice với user
        if ($users->count() > 0) {
            $sampleUser = $users->first();
            $priceWithUser = $sampleVariant->getFirstItemPrice(null, $sampleUser->user_id);
            echo "Giá variant với user: \${$priceWithUser}\n";
        }

        // Test getOrderPriceInfo với user
        if ($users->count() > 0) {
            $sampleUser = $users->first();
            $orderInfo = $sampleVariant->getOrderPriceInfo(null, 1, $sampleUser->user_id);
            echo "Order info với user: " . json_encode($orderInfo, JSON_PRETTY_PRINT) . "\n";
        }

        // Test getAllTierPrices
        $allTierPrices = $sampleVariant->getAllTierPrices();
        echo "Tất cả giá theo tier cho variant: " . count($allTierPrices) . " tiers\n";
    } else {
        echo "❌ Không có variant nào có shipping prices\n";
    }

    echo "\n✅ Hoàn tất test hệ thống giá theo Tier!\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
