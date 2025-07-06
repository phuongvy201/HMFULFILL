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
            $table->enum('tier_name', ['Wood', 'Silver', 'Gold', 'Diamond'])->nullable()->after('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->dropColumn('tier_name');
        });
    }
};
