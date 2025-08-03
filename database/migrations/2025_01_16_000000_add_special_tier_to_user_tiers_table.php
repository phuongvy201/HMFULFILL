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
        // Thêm 'Special' vào enum tier
        DB::statement("ALTER TABLE user_tiers MODIFY COLUMN tier ENUM('Wood', 'Silver', 'Gold', 'Diamond', 'Special')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa tất cả records có tier 'Special' trước khi rollback
        DB::table('user_tiers')->where('tier', 'Special')->delete();

        // Rollback tier enum về trạng thái cũ
        DB::statement("ALTER TABLE user_tiers MODIFY COLUMN tier ENUM('Wood', 'Silver', 'Gold', 'Diamond')");
    }
};
