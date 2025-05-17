<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Image;
use App\Models\ProductCountry;
use App\Models\Country;
use App\Models\AttributeValue;
use App\Models\Variant;
use App\Models\VariantAttributeValue;
use App\Models\Attribute;
use App\Models\ShippingPrice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductWithVariantsSeeder extends Seeder
{
    public function run()
    {
        // Tạo một số sản phẩm mẫu
        for ($i = 1; $i <= 5; $i++) {
            // Tạo sản phẩm cơ bản
            $product = Product::create([
                'name' => 'Sản phẩm mẫu ' . $i,
                'category_id' => rand(1, 3), // Giả sử bạn có 3 danh mục
                'status' => 1,
                'base_price' => rand(100000, 500000),
                'template_link' => 'https://example.com/template' . $i,
                'description' => 'Mô tả chi tiết cho sản phẩm mẫu ' . $i,
                'slug' => Str::slug('Sản phẩm mẫu ' . $i),
            ]);

            // Thêm fulfillment locations
            $countries = ['UK'];
            foreach (array_rand($countries, 2) as $index) {
                $product->fulfillmentLocations()->create([
                    'country_code' => $countries[$index]
                ]);
            }

            // Thêm variants
            $variantCount = rand(1, 3); // Mỗi sản phẩm có 1-3 biến thể
            for ($j = 1; $j <= $variantCount; $j++) {
                // Tạo variant
                $variant = $product->variants()->create([
                    'sku' => 'SKU-' . $i . '-' . $j,
                    'twofifteen_sku' => '215-SKU-' . $i . '-' . $j,
                    'flashship_sku' => 'FS-SKU-' . $i . '-' . $j
                ]);

                // Thêm shipping prices
                $shippingPrices = [
                    ['method' => ShippingPrice::METHOD_TIKTOK_1ST, 'price' => rand(20000, 50000)],
                    ['method' => ShippingPrice::METHOD_TIKTOK_NEXT, 'price' => rand(10000, 30000)],
                    ['method' => ShippingPrice::METHOD_SELLER_1ST, 'price' => rand(25000, 55000)],
                    ['method' => ShippingPrice::METHOD_SELLER_NEXT, 'price' => rand(15000, 35000)]
                ];

                foreach ($shippingPrices as $shipping) {
                    $variant->shippingPrices()->create([
                        'method' => $shipping['method'],
                        'price' => $shipping['price']
                    ]);
                }

                // Thêm attributes
                $attributes = [
                    ['name' => 'Màu sắc', 'value' => ['Đỏ', 'Xanh', 'Vàng', 'Đen'][rand(0, 3)]],
                    ['name' => 'Kích thước', 'value' => ['S', 'M', 'L', 'XL'][rand(0, 3)]]
                ];

                foreach ($attributes as $attribute) {
                    $variant->attributes()->create([
                        'name' => $attribute['name'],
                        'value' => $attribute['value']
                    ]);
                }
            }

            // Thêm hình ảnh
            $imageCount = rand(1, 4); // Mỗi sản phẩm có 1-4 hình ảnh
            for ($k = 1; $k <= $imageCount; $k++) {
                $product->images()->create([
                    'image_url' => 'images/products/sample_' . $i . '_' . $k . '.jpg'
                ]);
            }
        }

        Log::info('Đã tạo xong dữ liệu sản phẩm mẫu');
    }
}
