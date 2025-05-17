<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Clothing', 'slug' => 'clothing', 'parent_id' => null],
            ['name' => 'Accessories', 'slug' => 'accessories', 'parent_id' => null],
            ['name' => 'Footwear', 'slug' => 'footwear', 'parent_id' => null],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
