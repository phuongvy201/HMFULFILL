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
        Schema::create('user_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tier', ['Diamond', 'Gold', 'Silver', 'Wood']);
            $table->integer('order_count'); // Số đơn hàng trong tháng
            $table->date('effective_month'); // Tháng có hiệu lực (YYYY-MM-01)
            $table->timestamps();

            // Đảm bảo mỗi user chỉ có 1 tier cho mỗi tháng
            $table->unique(['user_id', 'effective_month']);

            // Index để tìm kiếm nhanh
            $table->index(['user_id', 'effective_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tiers');
    }
};
