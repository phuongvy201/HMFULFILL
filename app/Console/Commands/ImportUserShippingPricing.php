<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserPricingImportService;
use Illuminate\Support\Facades\Log;

class ImportUserShippingPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user-pricing 
                            {file : Đường dẫn đến file Excel}
                            {--dry-run : Chỉ kiểm tra file mà không import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import user shipping pricing từ file Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');

        if (!file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            return 1;
        }

        $this->info("Bắt đầu import user pricing...");
        $this->info("File: {$filePath}");
        $this->info("Dry run: " . ($isDryRun ? 'Có' : 'Không'));

        try {
            $service = new UserPricingImportService();

            if ($isDryRun) {
                $this->info("Chế độ dry-run: Chỉ kiểm tra file");
                // TODO: Implement dry-run functionality
                $this->info("Tính năng dry-run sẽ được implement sau");
                return 0;
            }

            // Tạo file object từ path
            $file = new \Illuminate\Http\UploadedFile($filePath, basename($filePath));

            $results = $service->importFromFile($file);

            $this->info("Import hoàn thành!");
            $this->info("Tổng dòng: {$results['total_rows']}");
            $this->info("Thành công: {$results['success_count']}");
            $this->info("Lỗi: {$results['error_count']}");

            if ($results['error_count'] > 0) {
                $this->warn("Chi tiết lỗi:");
                foreach ($results['errors'] as $error) {
                    $this->error("Dòng {$error['row']}: {$error['message']}");
                }
            }

            if ($results['success_count'] > 0) {
                $this->info("Chi tiết thành công:");
                foreach ($results['details'] as $detail) {
                    $this->line("Dòng {$detail['row']}: {$detail['variant_sku']} - {$detail['method']} - {$detail['price']} {$detail['currency']}");
                    foreach ($detail['processed_users'] as $user) {
                        $this->line("  - {$user}");
                    }
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Lỗi: " . $e->getMessage());
            Log::error('Import user pricing error: ' . $e->getMessage());
            return 1;
        }
    }
}
