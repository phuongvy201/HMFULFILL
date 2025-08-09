<?php

// Test script để kiểm tra logic product detail
require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use App\Models\ShippingPrice;

// Test data
echo "=== TEST PRODUCT DETAIL LOGIC ===\n";

// 1. Kiểm tra sản phẩm có variants không
$products = Product::with(['variants.attributes', 'variants.shippingPrices'])->get();

foreach ($products as $product) {
    echo "\nProduct: {$product->name} (ID: {$product->id})\n";
    echo "Variants count: " . $product->variants->count() . "\n";

    foreach ($product->variants as $variant) {
        echo "  - Variant ID: {$variant->id}, SKU: {$variant->sku}\n";
        echo "    Attributes: " . $variant->attributes->count() . "\n";
        echo "    Shipping Prices: " . $variant->shippingPrices->count() . "\n";

        foreach ($variant->attributes as $attr) {
            echo "      * {$attr->name}: {$attr->value}\n";
        }

        foreach ($variant->shippingPrices as $price) {
            echo "      * {$price->method}: {$price->price} {$price->currency}\n";
        }
    }

    // Test getGroupedAttributes
    $grouped = $product->getGroupedAttributes();
    echo "  Grouped Attributes:\n";
    foreach ($grouped as $name => $values) {
        echo "    * {$name}: " . implode(', ', $values->toArray()) . "\n";
    }
}

echo "\n=== END TEST ===\n";
