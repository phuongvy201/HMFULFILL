<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTransactionMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Đầu tiên, thay đổi kiểu dữ liệu của cột method thành string với độ dài đủ lớn
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('method', 50)->nullable()->change();
        });

        // Cập nhật các giá trị hiện có
        DB::table('transactions')
            ->where('method', 'VND')
            ->update(['method' => 'Bank VN']);

        DB::table('transactions')
            ->where('method', 'Paypal')
            ->update(['method' => 'Paypal']);

        DB::table('transactions')
            ->where('method', 'PingPong')
            ->update(['method' => 'PingPong']);

        DB::table('transactions')
            ->where('method', 'LianLianPay')
            ->update(['method' => 'LianLianPay']);

        DB::table('transactions')
            ->where('method', 'Worldfirst')
            ->update(['method' => 'WorldFirst']);

        DB::table('transactions')
            ->where('method', 'Payoneer')
            ->update(['method' => 'Payoneer']);

        // Sau khi cập nhật, chuyển đổi cột thành enum với các giá trị hợp lệ
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('method', ['Bank VN', 'Paypal', 'PingPong', 'LianLianPay', 'WorldFirst', 'Payoneer'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Chuyển đổi cột về string trước khi khôi phục giá trị cũ
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('method', 50)->nullable()->change();
        });

        // Khôi phục các giá trị cũ
        DB::table('transactions')
            ->where('method', 'Bank VN')
            ->update(['method' => 'VND']);

        DB::table('transactions')
            ->where('method', 'Paypal')
            ->update(['method' => 'Paypal']);

        DB::table('transactions')
            ->where('method', 'PingPong')
            ->update(['method' => 'PingPong']);

        DB::table('transactions')
            ->where('method', 'LianLianPay')
            ->update(['method' => 'LianLianPay']);

        DB::table('transactions')
            ->where('method', 'WorldFirst')
            ->update(['method' => 'Worldfirst']);

        DB::table('transactions')
            ->where('method', 'Payoneer')
            ->update(['method' => 'Payoneer']);
    }
}
