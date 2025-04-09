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
        Schema::create('excel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('external_id');
            $table->string('brand');
            $table->string('channel');
            $table->string('buyer_email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city');
            $table->string('county')->nullable();
            $table->string('post_code');
            $table->string('country');
            $table->string('phone1');
            $table->string('phone2')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->json('api_response')->nullable();
            $table->unsignedBigInteger('import_file_id');
            $table->timestamps();

            $table->foreign('import_file_id')->references('id')->on('import_files');
        });

        Schema::create('excel_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excel_order_id');
                $table->string('part_number');
                $table->string('title');
                $table->integer('quantity');
                $table->text('description')->nullable();
                $table->string('label_name')->nullable();
                $table->string('label_type')->nullable();
            $table->timestamps();

            $table->foreign('excel_order_id')->references('id')->on('excel_orders')->onDelete('cascade');
        });

        Schema::create('excel_order_mockups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excel_order_item_id');
            $table->string('title');
            $table->string('url');
            $table->timestamps();

            $table->foreign('excel_order_item_id')->references('id')->on('excel_order_items')->onDelete('cascade');
        });

        Schema::create('excel_order_designs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excel_order_item_id');
            $table->string('title');
            $table->string('url');
            $table->timestamps();

            $table->foreign('excel_order_item_id')->references('id')->on('excel_order_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_order_designs');
        Schema::dropIfExists('excel_order_mockups');
        Schema::dropIfExists('excel_order_items');
        Schema::dropIfExists('excel_orders');
    }
};
