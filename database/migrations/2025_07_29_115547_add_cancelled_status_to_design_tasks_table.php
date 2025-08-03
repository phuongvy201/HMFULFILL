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
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'joined', 'completed', 'approved', 'revision', 'cancelled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'joined', 'completed', 'approved', 'revision'])->default('pending')->change();
        });
    }
};
