<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FulfillmentRegion extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fulfillmentRegions = [
            [
                'name' => 'United States',
                'slug' => 'united-states',
                'description' => 'United States',
                'image_url' => 'https://media.istockphoto.com/id/961747352/vi/vec-to/c%E1%BB%9D-hoa-k%E1%BB%B3-minh-h%E1%BB%8Da-vector.jpg?s=612x612&w=0&k=20&c=r4KOoQnof8ssQs1XZH_9mZnaX7vtwid7GWIJu2wbhZg='
            ],
            [
                'name' => 'United Kingdom',
                'slug' => 'united-kingdom',
                'description' => 'United Kingdom',
                'image_url' => 'https://media.istockphoto.com/id/497118178/vi/vec-to/qu%E1%BB%91c-k%E1%BB%B3-v%C6%B0%C6%A1ng-qu%E1%BB%91c-anh.jpg?s=612x612&w=0&k=20&c=M2LHBmvt4SJ70vRavbdzf_A1HPX-wyRITivhNuryXqk='
            ],
            [
                'name' => 'Vietnam',
                'slug' => 'vietnam',
                'description' => 'Vietnam',
                'image_url' => 'https://media.istockphoto.com/id/1348446031/vi/vec-to/qu%E1%BB%91c-k%E1%BB%B3-vi%E1%BB%87t-nam.jpg?s=612x612&w=0&k=20&c=5ljYa6fMcoDDTpP0tihFfv2BJ2NnQ8uyKrzDVC-xaTE='
            ],

        ];

        // Thêm 10 người dùng ngẫu nhiên


        DB::table('fulfillment_regions')->insert($fulfillmentRegions);
    }
}
