<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm 'refund' vào type enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('topup', 'deduct', 'refund')");

        // Thêm 'USD' vào method enum nếu chưa có
        DB::statement("ALTER TABLE transactions MODIFY COLUMN method ENUM('Bank Vietnam', 'Payoneer', 'PingPong', 'LianLianPay', 'WorldFirst', 'PayPal', 'Bank VN', 'USD') NULL");

        // Thêm các cột mới nếu chưa tồn tại
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa tất cả records có type 'refund' trước khi rollback
        DB::table('transactions')->where('type', 'refund')->delete();

        // Rollback type enum về trạng thái cũ
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('topup', 'deduct')");

        // Rollback method enum (bỏ 'USD' và 'Bank VN')
        DB::statement("ALTER TABLE transactions MODIFY COLUMN method ENUM('Bank Vietnam', 'Payoneer', 'PingPong', 'LianLianPay', 'WorldFirst', 'PayPal') NULL");

        // Xóa các cột đã thêm
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
