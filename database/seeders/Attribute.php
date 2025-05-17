<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Attribute extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Color',

            ],
            [
                'name' => 'Size',

            ],
            [
                'name' => 'Material',

            ],
            [
                'name' => 'Style',

            ],
            [
                'name' => 'Pattern',

            ],
        ];

        // Thêm 10 người dùng ngẫu nhiên


        DB::table('attributes')->insert($attributes);
    }
}
