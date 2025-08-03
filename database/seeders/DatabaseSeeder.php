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

        $this->call([
            RefundTransactionSeeder::class,
            DesignerSeeder::class,
        ]);
    }
}
