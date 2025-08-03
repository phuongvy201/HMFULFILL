<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm 'Special' vào enum tier_name
        DB::statement("ALTER TABLE shipping_prices MODIFY COLUMN tier_name ENUM('Wood', 'Silver', 'Gold', 'Diamond', 'Special') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa tất cả records có tier_name 'Special' trước khi rollback
        DB::table('shipping_prices')->where('tier_name', 'Special')->delete();

        // Rollback tier_name enum về trạng thái cũ
        DB::statement("ALTER TABLE shipping_prices MODIFY COLUMN tier_name ENUM('Wood', 'Silver', 'Gold', 'Diamond') NULL");
    }
};
