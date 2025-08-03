<?php

namespace App\Console\Commands;

use App\Models\ImportFile;
use App\Models\ExcelOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseOnHoldOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:release-on-hold {--dry-run : Chạy thử mà không thực sự cập nhật}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chuyển trạng thái các đơn hàng từ on hold sang pending sau 1 tiếng (gộp cả ImportFile và individual orders)';

    /**
     * Thời gian chờ trước khi release (tính bằng giờ)
     */
    private const HOLD_DURATION_HOURS = 1;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🔍 CHẠY THỬ - Không có dữ liệu nào được thay đổi');
        }

        $this->info('🚀 Bắt đầu xử lý các đơn hàng on hold...');

        $cutoffTime = Carbon::now()->subHours(self::HOLD_DURATION_HOURS);
        $this->info("⏰ Xử lý các đơn hàng được tạo trước: {$cutoffTime->format('Y-m-d H:i:s')}");

        try {
            DB::beginTransaction();

            // 1. Xử lý ImportFiles
            $importFileResults = $this->processImportFiles($cutoffTime, $isDryRun);

            // 2. Xử lý individual orders (không có import_file_id)
            $individualOrderResults = $this->processIndividualOrders($cutoffTime, $isDryRun);

            if (!$isDryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            // Hiển thị kết quả tổng hợp
            $this->displayResults($importFileResults, $individualOrderResults, $isDryRun);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorMessage = "❌ Lỗi nghiêm trọng khi xử lý: " . $e->getMessage();
            $this->error($errorMessage);

            Log::error('Critical error in ReleaseOnHoldOrders command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }

        return 0;
    }

    /**
     * Xử lý ImportFiles và orders thuộc file đó
     */
    private function processImportFiles(Carbon $cutoffTime, bool $isDryRun): array
    {
        $this->newLine();
        $this->info('📁 BƯỚC 1: Xử lý Import Files...');

        $importFiles = ImportFile::where('status', ImportFile::STATUS_ON_HOLD)
            ->where('created_at', '<=', $cutoffTime)
            ->with(['excelOrders' => function ($query) {
                $query->where('status', 'on hold');
            }])
            ->get();

        if ($importFiles->isEmpty()) {
            $this->info('   ℹ️  Không có import file nào cần xử lý.');
            return ['files' => 0, 'orders' => 0, 'errors' => []];
        }

        $processedFiles = 0;
        $processedOrders = 0;
        $errors = [];

        foreach ($importFiles as $importFile) {
            try {
                $ordersCount = $importFile->excelOrders->count();

                if (!$isDryRun) {
                    // Cập nhật import file
                    $importFile->update(['status' => ImportFile::STATUS_PENDING]);

                    // Cập nhật tất cả orders của file này
                    ExcelOrder::where('import_file_id', $importFile->id)
                        ->where('status', 'on hold')
                        ->update(['status' => 'pending']);
                }

                $processedFiles++;
                $processedOrders += $ordersCount;

                $this->line("   ✅ File: {$importFile->file_name} ({$ordersCount} orders)");

                if (!$isDryRun) {
                    Log::info('ImportFile released from hold', [
                        'import_file_id' => $importFile->id,
                        'file_name' => $importFile->file_name,
                        'orders_count' => $ordersCount,
                        'created_at' => $importFile->created_at
                    ]);
                }
            } catch (\Exception $e) {
                $errors[] = "File {$importFile->file_name}: {$e->getMessage()}";
                $this->error("   ❌ Lỗi với file {$importFile->file_name}: {$e->getMessage()}");

                Log::error('Error processing ImportFile', [
                    'import_file_id' => $importFile->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'files' => $processedFiles,
            'orders' => $processedOrders,
            'errors' => $errors
        ];
    }

    /**
     * Xử lý individual orders (không thuộc ImportFile nào)
     */
    private function processIndividualOrders(Carbon $cutoffTime, bool $isDryRun): array
    {
        $this->newLine();
        $this->info('📝 BƯỚC 2: Xử lý Individual Orders...');

        $orders = ExcelOrder::where('status', 'on hold')
            ->where('created_at', '<=', $cutoffTime)
            ->whereNull('import_file_id') // Chỉ lấy orders không thuộc file nào
            ->get();

        if ($orders->isEmpty()) {
            $this->info('   ℹ️  Không có individual order nào cần xử lý.');
            return ['orders' => 0, 'errors' => []];
        }

        $processedOrders = 0;
        $errors = [];

        foreach ($orders as $order) {
            try {
                if (!$isDryRun) {
                    $order->update(['status' => 'pending']);
                }

                $processedOrders++;
                $this->line("   ✅ Order #{$order->id} - {$order->external_id}");

                if (!$isDryRun) {
                    Log::info('Individual order released from hold', [
                        'order_id' => $order->id,
                        'external_id' => $order->external_id,
                        'created_at' => $order->created_at
                    ]);
                }
            } catch (\Exception $e) {
                $errors[] = "Order #{$order->id}: {$e->getMessage()}";
                $this->error("   ❌ Lỗi với order #{$order->id}: {$e->getMessage()}");

                Log::error('Error processing individual order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'orders' => $processedOrders,
            'errors' => $errors
        ];
    }

    /**
     * Hiển thị kết quả tổng hợp
     */
    private function displayResults(array $importFileResults, array $individualOrderResults, bool $isDryRun): void
    {
        $this->newLine();
        $this->info('📊 === KẾT QUẢ TỔNG HỢP ===');

        if ($isDryRun) {
            $this->warn('   (Kết quả chạy thử - không có dữ liệu nào được thay đổi)');
        }

        // Import Files
        $this->info("📁 Import Files:");
        $this->info("   • Số file xử lý: {$importFileResults['files']}");
        $this->info("   • Orders từ files: {$importFileResults['orders']}");

        // Individual Orders
        $this->info("📝 Individual Orders:");
        $this->info("   • Số order xử lý: {$individualOrderResults['orders']}");

        // Tổng cộng
        $totalOrders = $importFileResults['orders'] + $individualOrderResults['orders'];
        $totalErrors = count($importFileResults['errors']) + count($individualOrderResults['errors']);

        $this->info("🎯 Tổng cộng:");
        $this->info("   • Tổng orders xử lý: {$totalOrders}");
        $this->info("   • Tổng lỗi: {$totalErrors}");

        if ($totalErrors > 0) {
            $this->warn("⚠️  Có {$totalErrors} lỗi xảy ra. Kiểm tra log để biết chi tiết.");
        }

        if (!$isDryRun && $totalOrders > 0) {
            Log::info('ReleaseOnHoldOrders command completed', [
                'import_files_processed' => $importFileResults['files'],
                'import_file_orders_processed' => $importFileResults['orders'],
                'individual_orders_processed' => $individualOrderResults['orders'],
                'total_orders_processed' => $totalOrders,
                'total_errors' => $totalErrors,
                'execution_time' => now()
            ]);
        }

        $this->info('✅ Hoàn tất xử lý!');
    }
}
