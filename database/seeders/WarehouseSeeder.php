<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            ['name' => 'Two Fifteen', 'code' => '215', 'address' => 'UK', 'phone' => '1234567890', 'email' => 'warehouse1@example.com', 'is_active' => true],
            ['name' => 'Flash Ship', 'code' => 'FS', 'address' => 'VN', 'phone' => '1234567890', 'email' => 'warehouse2@example.com', 'is_active' => true],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
