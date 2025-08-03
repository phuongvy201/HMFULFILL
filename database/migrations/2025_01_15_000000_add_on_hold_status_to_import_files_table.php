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
        Schema::table('import_files', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processed', 'failed', 'pending_confirmation', 'on hold'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processed', 'failed', 'pending_confirmation'])
                ->default('pending')
                ->change();
        });
    }
};
