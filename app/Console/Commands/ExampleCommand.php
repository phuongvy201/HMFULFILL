<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExampleCommand extends Command
{
    /**
     * Tên và signature của console command.
     * 
     * Định nghĩa:
     * - Tên command: example:run
     * - Options: --force (tùy chọn)
     * - Arguments: {action} (bắt buộc)
     */
    protected $signature = 'example:run {action} {--force : Bắt buộc thực hiện mà không cần xác nhận}';

    /**
     * Mô tả của console command.
     */
    protected $description = 'Command ví dụ để minh họa cách tạo command từ đầu';

    /**
     * Constructor - Khởi tạo command
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Thực thi console command.
     * 
     * @return int Trả về 0 nếu thành công, khác 0 nếu lỗi
     */
    public function handle()
    {
        // Lấy argument và option
        $action = $this->argument('action');
        $force = $this->option('force');

        // Hiển thị thông tin bắt đầu
        $this->info('🚀 Bắt đầu thực hiện ExampleCommand...');
        $this->line("📝 Action: {$action}");
        $this->line("⚡ Force mode: " . ($force ? 'Yes' : 'No'));

        try {
            // Xử lý theo action
            switch ($action) {
                case 'test':
                    $this->handleTest($force);
                    break;

                case 'cleanup':
                    $this->handleCleanup($force);
                    break;

                case 'status':
                    $this->handleStatus();
                    break;

                default:
                    $this->error("❌ Action '{$action}' không được hỗ trợ!");
                    $this->info("💡 Các action có sẵn: test, cleanup, status");
                    return 1;
            }

            $this->info('✅ Command đã hoàn thành thành công!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Có lỗi xảy ra: ' . $e->getMessage());
            Log::error('ExampleCommand Error', [
                'action' => $action,
                'force' => $force,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Xử lý action test
     */
    private function handleTest($force)
    {
        $this->info('🧪 Đang thực hiện test...');

        if (!$force && !$this->confirm('Bạn có chắc chắn muốn chạy test?')) {
            $this->warn('⚠️ Test đã bị hủy bởi người dùng');
            return;
        }

        // Giả lập quá trình test với progress bar
        $bar = $this->output->createProgressBar(5);
        $bar->start();

        for ($i = 0; $i < 5; $i++) {
            sleep(1); // Giả lập công việc
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Test hoàn thành!');
    }

    /**
     * Xử lý action cleanup
     */
    private function handleCleanup($force)
    {
        $this->info('🧹 Đang thực hiện cleanup...');

        if (!$force && !$this->confirm('Bạn có chắc chắn muốn cleanup? Hành động này không thể hoàn tác!')) {
            $this->warn('⚠️ Cleanup đã bị hủy bởi người dùng');
            return;
        }

        // Giả lập quá trình cleanup
        $this->info('🗑️ Đang xóa temporary files...');
        $this->info('📁 Đang dọn dẹp cache...');
        $this->info('💾 Đang optimize database...');

        $this->info('✅ Cleanup hoàn thành!');
    }

    /**
     * Xử lý action status
     */
    private function handleStatus()
    {
        $this->info('📊 Hiển thị trạng thái hệ thống...');

        // Tạo bảng hiển thị thông tin
        $this->table(
            ['Thông số', 'Giá trị', 'Trạng thái'],
            [
                ['Database', 'Connected', '🟢 OK'],
                ['Cache', 'Redis', '🟢 OK'],
                ['Queue', 'Running', '🟢 OK'],
                ['Storage', '85% used', '🟡 Warning'],
                ['Memory', '2.1GB/4GB', '🟢 OK'],
            ]
        );

        $this->info('✅ Kiểm tra trạng thái hoàn thành!');
    }
}
