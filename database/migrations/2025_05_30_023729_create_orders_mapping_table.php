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
        Schema::create('orders_mapping', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->comment('Mã đơn hàng trên hệ thống của bạn');
            $table->string('internal_id')->comment('Mã đơn hàng bên xưởng');
            $table->string('factory')->comment('Tên xưởng: UK1, UK2, US, VN');
            $table->json('api_response')->nullable()->comment('Response từ API xưởng');
            $table->timestamps();

            // Indexes để tối ưu query
            $table->index('external_id');
            $table->index('internal_id');
            $table->index('factory');
            $table->index(['external_id', 'factory']);

            // Unique constraint cho external_id + factory
            $table->unique(['external_id', 'factory'], 'unique_external_factory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_mapping');
    }
};
