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
        Schema::create('excel_order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excel_order_id');
            $table->integer('total_quantity');
            $table->decimal('total_price', 15, 2);
            $table->enum('status', ['pending', 'success', 'error'])->default('pending');
            $table->longText('factory_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('excel_order_id')->references('id')->on('excel_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_order_fulfillments');
    }
};
