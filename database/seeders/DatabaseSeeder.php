<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Image;
use App\Models\AttributeValue;
use App\Models\ProductCountry;
use App\Models\Variant;
use App\Models\VariantAttributeValue;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Tạo sản phẩm
        $products = [
            [
                'name' => 'T-Shirt',
                'slug' => 't-shirt',
                'description' => 'Cotton T-Shirt available in multiple colors and sizes',
                'base_price' => 12.91,
                'seller_id' => 1,
                'category_id' => 1,
                'status' => 1,
                'template_link' => 'https://docs.google.com/spreadsheets/d/1bUscN4DSJWdRE9-gsFBuszvlX0vuzVfeyg0P-lpKqRw/edit?gid=1533091627#gid=1533091627',
            ],
            [
                'name' => 'Hoodie',
                'slug' => 'hoodie',
                'description' => 'Warm hoodie for winter',
                'base_price' => 12.91,
                'seller_id' => 1,
                'category_id' => 1,
                'status' => 1,
                'template_link' => 'https://docs.google.com/spreadsheets/d/1bUscN4DSJWdRE9-gsFBuszvlX0vuzVfeyg0P-lpKqRw/edit?gid=1533091627#gid=1533091627',
            ],
            [
                'name' => 'Sweatshirt',
                'slug' => 'sweatshirt',
                'description' => 'Warm sweatshirt for winter',
                'base_price' => 12.91,
                'seller_id' => 1,
                'category_id' => 1,
                'status' => 1,
                'template_link' => 'https://docs.google.com/spreadsheets/d/1bUscN4DSJWdRE9-gsFBuszvlX0vuzVfeyg0P-lpKqRw/edit?gid=1533091627#gid=1533091627',
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Tạo hình ảnh cho sản phẩm
            $images = [
                ['product_id' => $product->id, 'image_url' => 'https://www.twofifteen.co.uk/images/pictures/product-thumbnails/awdis-sweatshirt-2-(product).jpg?v=835b0b37', 'is_variant_image' => false],
                ['product_id' => $product->id, 'image_url' => 'https://www.twofifteen.co.uk/images/pictures/product-thumbnails/ks-(product).jpg?v=d530ab87', 'is_variant_image' => false],
            ];

            foreach ($images as $imageData) {
                $image = $product->images()->create($imageData);
            }


            // Tạo quốc gia cho sản phẩm
            $productCountries = [
                ['country_id' => 1], // Giả sử country_id là 1
                ['country_id' => 2], // Giả sử country_id là 2
                ['country_id' => 3], // Giả sử country_id là 3
            ];

            foreach ($productCountries as $productCountryData) {
                ProductCountry::create(array_merge($productCountryData, ['product_id' => $product->id]));
            }
        }
    }
}
