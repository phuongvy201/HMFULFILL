<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            // Xóa cột tier_name nếu tồn tại
            if (Schema::hasColumn('shipping_prices', 'tier_name')) {
                $table->dropColumn('tier_name');
            }

            // Xóa cột user_id nếu tồn tại
            if (Schema::hasColumn('shipping_prices', 'user_id')) {
                // Xóa cột trực tiếp, Laravel sẽ tự động xử lý foreign key
                $table->dropColumn('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            // Thêm lại cột tier_name
            $table->enum('tier_name', ['Wood', 'Silver', 'Gold', 'Diamond', 'Special'])->nullable()->after('method');

            // Thêm lại cột user_id
            $table->unsignedBigInteger('user_id')->nullable()->after('tier_name');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Thêm lại indexes
            $table->index(['variant_id', 'method', 'user_id']);
            $table->index(['variant_id', 'method', 'tier_name', 'user_id']);
        });
    }
};
