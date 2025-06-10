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
            // 1. Xóa cột tracking_number nếu tồn tại
            if (Schema::hasColumn('excel_orders', 'tracking_number')) {
                $table->dropColumn('tracking_number');
            }

            // 2. Thêm cột warehouse (loại string, cho phép NULL)
            if (! Schema::hasColumn('excel_orders', 'warehouse')) {
                $table->string('warehouse')->nullable()->after('import_file_id')
                    ->comment('Kho hàng/nhà cung cấp');
            }

            // 3. Cho phép import_file_id được NULL
            if (Schema::hasColumn('excel_orders', 'import_file_id')) {
                $table->unsignedBigInteger('import_file_id')->nullable()->change();
            }

            // 4. Thêm cột created_by (liên kết đến users.id), cho phép NULL
            if (! Schema::hasColumn('excel_orders', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('import_file_id')
                    ->comment('ID người tạo đơn (nếu là đơn thủ công)');
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('excel_orders', function (Blueprint $table) {
            // 1. Thêm lại cột tracking_number (string, nullable)
            if (! Schema::hasColumn('excel_orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('status');
            }

            // 2. Xóa cột warehouse nếu tồn tại
            if (Schema::hasColumn('excel_orders', 'warehouse')) {
                $table->dropColumn('warehouse');
            }

            // 3. Đặt import_file_id về NOT NULL (nếu cột tồn tại)
            if (Schema::hasColumn('excel_orders', 'import_file_id')) {
                $table->unsignedBigInteger('import_file_id')->nullable(false)->change();
            }

            // 4. Xóa foreign key và cột created_by nếu tồn tại
            if (Schema::hasColumn('excel_orders', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
