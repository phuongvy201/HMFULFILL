<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('refunded_by')->nullable()->after('refunded_at');
            $table->unsignedBigInteger('refund_transaction_id')->nullable()->after('refunded_by');

            $table->foreign('refunded_by')->references('id')->on('users');
            $table->foreign('refund_transaction_id')->references('id')->on('transactions');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['refunded_by']);
            $table->dropForeign(['refund_transaction_id']);
            $table->dropColumn(['refunded_at', 'refunded_by', 'refund_transaction_id']);
        });
    }
};
