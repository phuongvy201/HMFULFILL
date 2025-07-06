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
            $table->unsignedBigInteger('user_id');
            $table->enum('tier', ['Wood', 'Silver', 'Gold', 'Diamond']);
            $table->integer('order_count')->default(0); // Số đơn hàng trong tháng
            $table->date('month'); // ngày đại diện cho tháng, ví dụ: '2024-07-01'
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint - mỗi user chỉ có 1 tier mỗi tháng
            $table->unique(['user_id', 'month']);
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
