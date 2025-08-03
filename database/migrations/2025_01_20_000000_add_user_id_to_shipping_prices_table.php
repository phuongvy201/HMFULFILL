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
            $table->unsignedBigInteger('user_id')->nullable()->after('tier_name');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Index để tìm kiếm nhanh
            $table->index(['variant_id', 'method', 'user_id']);
            $table->index(['variant_id', 'method', 'tier_name', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['variant_id', 'method', 'user_id']);
            $table->dropIndex(['variant_id', 'method', 'tier_name', 'user_id']);
            $table->dropColumn('user_id');
        });
    }
};
