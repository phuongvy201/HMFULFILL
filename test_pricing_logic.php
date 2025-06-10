<?php

require_once 'bootstrap/app.php';

use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Category;
use App\Models\ShippingPrice;

// Tạo dữ liệu test
echo "=== TEST LOGIC TÍNH GIÁ MỚI ===\n\n";

// Giả lập dữ liệu test case
$testProducts = [
    // Test Case 1: 2 áo sweatshirt giống nhau
    ['category' => 'sweatshirt', 'price_1st' => 15.0, 'price_next' => 10.0, 'quantity' => 2],

    // Test Case 2: 2 áo hoodie + 1 áo sweatshirt (hoodie có giá cao nhất)
    ['category' => 'hoodie', 'price_1st' => 20.0, 'price_next' => 15.0, 'quantity' => 2],
    ['category' => 'sweatshirt', 'price_1st' => 15.0, 'price_next' => 10.0, 'quantity' => 1],

    // Test Case 3: 1 áo hoodie + 1 áo t-shirt (hoodie có giá cao hơn)
    ['category' => 'hoodie', 'price_1st' => 20.0, 'price_next' => 15.0, 'quantity' => 1],
    ['category' => 'tshirt', 'price_1st' => 12.0, 'price_next' => 8.0, 'quantity' => 1],

    // Test Case 4: 2 sweatshirt + 2 tshirt với TikTok (sweatshirt có giá cao nhất)
    ['category' => 'sweatshirt', 'price_1st' => 18.0, 'price_next' => 12.0, 'quantity' => 2],
    ['category' => 'tshirt', 'price_1st' => 12.0, 'price_next' => 8.0, 'quantity' => 2],
];

function runTestCase($testName, $products, $shippingMethod)
{
    echo "--- $testName ---\n";
    echo "Shipping Method: $shippingMethod\n";

    // Chuẩn bị dữ liệu đầu vào
    $allProducts = [];
    foreach ($products as $index => $productData) {
        // Tạo mock variant
        $mockVariant = new class {
            public $category_type;
            public $prices;

            public function getCategoryType()
            {
                return $this->category_type;
            }

            public function getPriceByMethod($method)
            {
                return $this->prices[$method] ?? 0;
            }
        };

        $mockVariant->category_type = $productData['category'];
        $mockVariant->prices = [
            'tiktok_1st' => $productData['price_1st'],
            'tiktok_next' => $productData['price_next'],
            'seller_1st' => $productData['price_1st'],
            'seller_next' => $productData['price_next'],
        ];

        $allProducts[] = [
            'variant' => $mockVariant,
            'quantity' => $productData['quantity'],
            'original_index' => $index,
        ];
    }

    // Sử dụng logic tính giá
    $result = calculateOrderPricingTest($allProducts, $shippingMethod);

    echo "Total Amount: $" . $result['total_amount'] . "\n";
    echo "Breakdown:\n";

    foreach ($result['item_prices'] as $index => $item) {
        $productData = $products[$index];
        echo "  Item $index ({$item['category_type']}): ";
        echo "Qty {$item['quantity']} x \${$item['unit_price']} = \${$item['total_price']} ";
        echo "({$item['price_type']})\n";
    }
    echo "\n";
}

function calculateOrderPricingTest(array $products, ?string $shippingMethod = null): array
{
    $results = [];
    $totalAmount = 0;

    // Xác định loại shipping
    $shippingMethodLower = strtolower((string)$shippingMethod);
    $isTikTokLabel = str_contains($shippingMethodLower, 'tiktok_label');

    $firstItemMethod = $isTikTokLabel ? 'tiktok_1st' : 'seller_1st';
    $nextItemMethod = $isTikTokLabel ? 'tiktok_next' : 'seller_next';

    // Bước 1: Nhóm sản phẩm theo category
    $groupedByCategory = [];
    foreach ($products as $index => $product) {
        $variant = $product['variant'];
        $categoryType = $variant->getCategoryType();

        if (!isset($groupedByCategory[$categoryType])) {
            $groupedByCategory[$categoryType] = [];
        }

        $groupedByCategory[$categoryType][] = [
            'index' => $index,
            'variant' => $variant,
            'quantity' => $product['quantity'],
            'product_data' => $product,
            'first_item_price' => $variant->getPriceByMethod($firstItemMethod)
        ];
    }

    // Bước 2: Tìm category có giá cao nhất trong toàn bộ đơn hàng
    $highestPriceCategory = null;
    $highestPrice = 0;

    foreach ($groupedByCategory as $categoryType => $categoryProducts) {
        foreach ($categoryProducts as $productInfo) {
            if ($productInfo['first_item_price'] > $highestPrice) {
                $highestPrice = $productInfo['first_item_price'];
                $highestPriceCategory = $categoryType;
            }
        }
    }

    // Bước 3: Tính giá cho từng nhóm category
    foreach ($groupedByCategory as $categoryType => $categoryProducts) {
        // Sắp xếp sản phẩm trong category theo giá giảm dần
        usort($categoryProducts, function ($a, $b) {
            return $b['first_item_price'] <=> $a['first_item_price'];
        });

        $isHighestPriceCategory = ($categoryType === $highestPriceCategory);
        $categoryHasUsedFirstPrice = false;

        foreach ($categoryProducts as $categoryIndex => $productInfo) {
            $variant = $productInfo['variant'];
            $quantity = $productInfo['quantity'];
            $originalIndex = $productInfo['index'];

            // Xác định giá cho sản phẩm này
            if ($isHighestPriceCategory && !$categoryHasUsedFirstPrice) {
                // Sản phẩm đầu tiên trong category có giá cao nhất -> dùng giá first item
                $unitPrice = $variant->getPriceByMethod($firstItemMethod);
                $categoryHasUsedFirstPrice = true;
                $priceType = 'first_item';
            } else {
                // Tất cả sản phẩm khác dùng giá next item  
                $unitPrice = $variant->getPriceByMethod($nextItemMethod);
                $priceType = 'next_item';
            }

            $itemTotal = $unitPrice * $quantity;
            $totalAmount += $itemTotal;

            $results[$originalIndex] = [
                'category_type' => $categoryType,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $itemTotal,
                'price_type' => $priceType,
                'method_used' => $priceType === 'first_item' ? $firstItemMethod : $nextItemMethod,
                'is_highest_price_category' => $isHighestPriceCategory
            ];
        }
    }

    return [
        'item_prices' => $results,
        'total_amount' => round($totalAmount, 2),
        'shipping_method' => $shippingMethod,
        'is_tiktok_label' => $isTikTokLabel
    ];
}

// Chạy các test case
runTestCase("TH1: 2 áo sweatshirt giống nhau", [
    ['category' => 'sweatshirt', 'price_1st' => 15.0, 'price_next' => 10.0, 'quantity' => 2]
], 'seller');

runTestCase("TH2: 2 hoodie + 1 sweatshirt (hoodie có giá cao nhất)", [
    ['category' => 'hoodie', 'price_1st' => 20.0, 'price_next' => 15.0, 'quantity' => 2],
    ['category' => 'sweatshirt', 'price_1st' => 15.0, 'price_next' => 10.0, 'quantity' => 1]
], 'seller');

runTestCase("TH3: 1 hoodie + 1 tshirt (hoodie có giá cao hơn)", [
    ['category' => 'hoodie', 'price_1st' => 20.0, 'price_next' => 15.0, 'quantity' => 1],
    ['category' => 'tshirt', 'price_1st' => 12.0, 'price_next' => 8.0, 'quantity' => 1]
], 'seller');

runTestCase("TH4: 2 sweatshirt + 2 tshirt với TikTok (sweatshirt có giá cao nhất)", [
    ['category' => 'sweatshirt', 'price_1st' => 18.0, 'price_next' => 12.0, 'quantity' => 2],
    ['category' => 'tshirt', 'price_1st' => 12.0, 'price_next' => 8.0, 'quantity' => 2]
], 'tiktok_label');

echo "=== HOÀN THÀNH TEST ===\n";
