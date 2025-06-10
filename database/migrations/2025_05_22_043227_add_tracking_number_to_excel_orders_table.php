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
        Schema::table('excel_orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('import_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('excel_orders', function (Blueprint $table) {
            $table->dropColumn('tracking_number');
        });
    }
};
