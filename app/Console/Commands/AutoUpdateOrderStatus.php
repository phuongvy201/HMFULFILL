<?php

namespace App\Console\Commands;

use App\Models\ExcelOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoUpdateOrderStatus extends Command
{
    /**
     * Tên và mô tả của command
     */
    protected $signature = 'orders:update-status';
    protected $description = 'Cập nhật trạng thái đơn hàng từ "on hold" sang "pending" sau 1 giờ';

    /**
     * Thời gian chờ trước khi cập nhật trạng thái (tính bằng phút)
     */
    private const HOLD_DURATION_HOURS = 1;

    /**
     * Thực thi command
     */
    public function handle()
    {   
        $this->info('Bắt đầu cập nhật trạng thái đơn hàng...');

        try {
            // Lấy thời điểm 1 giờ trước
            $cutoffTime = Carbon::now()->subHour(self::HOLD_DURATION_HOURS);

            // Tìm các đơn hàng có trạng thái "on hold" và được tạo từ 1 giờ trước
            $orders = ExcelOrder::where('status', 'on hold')
                ->where('created_at', '<=', $cutoffTime)
                ->get();

            $updatedCount = 0;
            $errors = [];

            foreach ($orders as $order) {
                try {
                    // Lưu trạng thái cũ để log
                    $oldStatus = $order->status;

                    // Cập nhật trạng thái
                    $order->status = 'pending';
                    $order->save();

                    $updatedCount++;

                    // Log thông tin cập nhật
                    Log::info("Đơn hàng #{$order->id} đã được cập nhật từ '{$oldStatus}' sang 'pending'", [
                        'order_id' => $order->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'pending',
                        'updated_at' => now()->toDateTimeString()
                    ]);

                    $this->line("✓ Đã cập nhật đơn hàng #{$order->id}");
                } catch (\Exception $e) {
                    $errorMessage = "Lỗi khi cập nhật đơn hàng #{$order->id}: " . $e->getMessage();
                    $errors[] = $errorMessage;

                    Log::error($errorMessage, [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $this->error("✗ " . $errorMessage);
                }
            }

            // Hiển thị kết quả tổng quan
            $this->newLine();
            $this->info("=== KẾT QUẢ CẬP NHẬT ===");
            $this->info("Tổng số đơn hàng được xử lý: " . $orders->count());
            $this->info("Số đơn hàng cập nhật thành công: " . $updatedCount);
            $this->info("Số lỗi: " . count($errors));

            if (count($errors) > 0) {
                $this->warn("Có " . count($errors) . " lỗi xảy ra. Vui lòng kiểm tra log để biết chi tiết.");
            }

            // Log tổng kết
            Log::info("Hoàn thành cập nhật trạng thái đơn hàng", [
                'total_processed' => $orders->count(),
                'successful_updates' => $updatedCount,
                'errors' => count($errors),
                'execution_time' => now()->toDateTimeString()
            ]);

            $this->info('Cập nhật trạng thái đơn hàng hoàn tất!');
        } catch (\Exception $e) {
            $errorMessage = "Lỗi nghiêm trọng khi cập nhật trạng thái đơn hàng: " . $e->getMessage();

            Log::error($errorMessage, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error($errorMessage);
            return 1; // Trả về exit code lỗi
        }

        return 0; // Trả về exit code thành công
    }
}
