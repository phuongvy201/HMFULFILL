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
        Schema::create('design_tasks', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại thủ công để tránh lỗi trên SQLite
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('designer_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sides_count');
            $table->decimal('price', 8, 2); // SQLite vẫn chấp nhận

            // SQLite không hỗ trợ enum → dùng string
            $table->string('status')->default('pending');

            $table->string('mockup_file')->nullable();
            $table->string('design_file')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Khóa ngoại (nếu SQLite bật hỗ trợ)
            // Có thể bỏ qua trong development/test nếu cần
            // $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('designer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_tasks');
    }
};
