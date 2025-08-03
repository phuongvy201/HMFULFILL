<?php

// Test logic lấy ảnh chính của sản phẩm
require_once __DIR__ . '/vendor/autoload.php';

// Khởi tạo Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Log;

// Test method getMainImageUrl()
echo "=== Test Main Image Logic ===\n";

// Lấy một sản phẩm bất kỳ có ảnh
$product = Product::whereHas('images')->first();

if ($product) {
    echo "Product ID: {$product->id}\n";
    echo "Product Name: {$product->name}\n";
    echo "Total Images: " . $product->images->count() . "\n";
    echo "Has Images: " . ($product->hasImages() ? 'Yes' : 'No') . "\n";
    echo "Main Image URL: " . ($product->getMainImageUrl() ?? 'null') . "\n";

    // Hiển thị danh sách ảnh theo thứ tự created_at
    echo "\nImages by created_at (oldest first):\n";
    foreach ($product->images as $index => $image) {
        $isMain = $index === 0 ? ' (MAIN)' : '';
        echo "  {$index}. {$image->image_url} - {$image->created_at}{$isMain}\n";
    }
} else {
    echo "No products with images found\n";
}

// Test với một vài sản phẩm khác
echo "\n=== Test Multiple Products ===\n";
$products = Product::with('images')->take(3)->get();

foreach ($products as $product) {
    echo "Product {$product->id}: {$product->name}\n";
    echo "  Images: " . $product->images->count() . "\n";
    echo "  Main Image: " . ($product->getMainImageUrl() ?? 'null') . "\n";
}

echo "\nTest completed.\n";
