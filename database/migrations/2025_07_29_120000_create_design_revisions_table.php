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
        Schema::create('design_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('design_task_id'); // Khóa ngoại thủ công
            $table->unsignedBigInteger('designer_id'); // Khóa ngoại thủ công
            $table->string('design_file'); // File thiết kế
            $table->text('notes')->nullable(); // Ghi chú của designer
            $table->text('revision_notes')->nullable(); // Yêu cầu chỉnh sửa từ khách hàng
            $table->integer('version')->default(1); // Số phiên bản

            // Vì SQLite không hỗ trợ ENUM, dùng string và validate ở tầng ứng dụng
            $table->string('status')->default('submitted');

            $table->timestamp('submitted_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Khóa ngoại (chỉ được hỗ trợ nếu SQLite bật foreign key support)
            // Trong quá trình phát triển có thể không cần nếu SQLite không hỗ trợ
            $table->foreign('design_task_id')->references('id')->on('design_tasks')->onDelete('cascade');
            $table->foreign('designer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_revisions');
    }
};
