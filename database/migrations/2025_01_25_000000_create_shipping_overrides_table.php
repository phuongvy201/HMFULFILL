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
        Schema::create('shipping_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_price_id')
                ->constrained('shipping_prices')
                ->onDelete('cascade');

            // TEXT thay cho JSON để chạy được cả SQLite và MySQL
            $table->text('user_ids')
                ->nullable()
                ->comment('Mảng các user_id được áp dụng override này (JSON)');

            $table->string('tier_name')
                ->nullable()
                ->comment('Tier name nếu áp dụng cho tier');

            $table->decimal('override_price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();

            // Index hợp lệ
            $table->index(['shipping_price_id', 'tier_name']);
            $table->index('tier_name');

            // ❌ Bỏ index trực tiếp trên user_ids vì là TEXT/JSON
            // Nếu cần tìm kiếm nhanh trong user_ids -> nên tạo bảng phụ shipping_override_users
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_overrides');
    }
};
