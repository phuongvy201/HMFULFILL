<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingOverride;

class CleanupShippingOverrides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:cleanup-duplicates {--dry-run : Chỉ hiển thị kết quả, không xóa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dọn dẹp duplicate records trong shipping_overrides';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Bắt đầu kiểm tra duplicate shipping overrides...');

        if ($this->option('dry-run')) {
            $this->warn('⚠️  Chế độ DRY RUN - Chỉ hiển thị kết quả, không xóa gì');
        }

        $results = ShippingOverride::cleanupDuplicates();

        $this->info("📊 Kết quả kiểm tra:");
        $this->line("   - Tổng records đã kiểm tra: {$results['total_checked']}");
        $this->line("   - Duplicates tìm thấy: {$results['duplicates_found']}");
        $this->line("   - Duplicates đã xóa: {$results['duplicates_removed']}");

        if (!empty($results['details'])) {
            $this->info("\n📋 Chi tiết duplicates:");
            foreach ($results['details'] as $detail) {
                $this->line("   - Shipping Price ID: {$detail['shipping_price_id']}");
                $this->line("     User IDs: {$detail['user_ids']}");
                $this->line("     Giữ lại Override ID: {$detail['kept_override_id']}");
                $this->line("     Đã xóa: {$detail['removed_count']} records");
                $this->line("");
            }
        }

        if ($results['duplicates_removed'] > 0) {
            $this->info("✅ Đã dọn dẹp thành công {$results['duplicates_removed']} duplicate records!");
        } else {
            $this->info("✅ Không tìm thấy duplicate records nào.");
        }

        return 0;
    }
}
