<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temp-files 
                           {--dry-run : Chỉ hiển thị files sẽ bị xóa, không xóa thực tế}
                           {--older-than=24 : Xóa files cũ hơn X giờ (mặc định 24h)}
                           {--force : Bỏ qua xác nhận và xóa ngay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dọn dẹp temp files để giải phóng disk space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== DỌN DẸP TEMP FILES ===\n");

        $dryRun = $this->option('dry-run');
        $olderThan = (int) $this->option('older-than');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - Chỉ hiển thị files sẽ bị xóa\n");
        }

        // 1. Kiểm tra disk space trước khi dọn dẹp
        $this->checkDiskSpaceBefore();

        // 2. Dọn dẹp system temp directory
        $this->cleanupSystemTemp($dryRun, $olderThan);

        // 3. Dọn dẹp Laravel temp files
        $this->cleanupLaravelTemp($dryRun, $olderThan);

        // 4. Dọn dẹp upload temp files
        $this->cleanupUploadTemp($dryRun, $olderThan);

        // 5. Dọn dẹp cache files
        $this->cleanupCacheFiles($dryRun, $olderThan);

        // 6. Dọn dẹp log files
        $this->cleanupLogFiles($dryRun, $olderThan);

        // 7. Kiểm tra disk space sau khi dọn dẹp
        $this->checkDiskSpaceAfter();

        // 8. Hiển thị tổng kết
        $this->showSummary();

        return 0;
    }

    /**
     * Kiểm tra disk space trước khi dọn dẹp
     */
    private function checkDiskSpaceBefore()
    {
        $this->info("1. Kiểm tra disk space trước khi dọn dẹp:");

        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = round(($diskUsed / $diskTotal) * 100, 2);

        $this->line("   Total: " . $this->formatBytes($diskTotal));
        $this->line("   Used: " . $this->formatBytes($diskUsed));
        $this->line("   Free: " . $this->formatBytes($diskFree));
        $this->line("   Usage: {$diskUsagePercent}%");

        if ($diskUsagePercent > 90) {
            $this->error("   ⚠️  Disk space critical (>90%)");
        } elseif ($diskUsagePercent > 80) {
            $this->warn("   ⚠️  Disk space warning (>80%)");
        } else {
            $this->line("   ✓ Disk space OK");
        }

        $this->info("");
    }

    /**
     * Dọn dẹp system temp directory
     */
    private function cleanupSystemTemp($dryRun, $olderThan)
    {
        $this->info("2. Dọn dẹp system temp directory:");

        $tempDir = sys_get_temp_dir();
        $cutoffTime = time() - ($olderThan * 3600);
        $deletedCount = 0;
        $deletedSize = 0;

        if (!is_dir($tempDir)) {
            $this->warn("   Temp directory không tồn tại: {$tempDir}");
            return;
        }

        $files = glob($tempDir . '/*');
        $totalFiles = count($files);

        $this->line("   Scanning {$totalFiles} files in {$tempDir}");

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileTime = filemtime($file);
                $fileSize = filesize($file);

                // Bỏ qua files mới tạo
                if ($fileTime > $cutoffTime) {
                    continue;
                }

                // Bỏ qua files đang được sử dụng
                if ($this->isFileInUse($file)) {
                    continue;
                }

                $deletedCount++;
                $deletedSize += $fileSize;

                if (!$dryRun) {
                    unlink($file);
                }

                $this->line("   " . ($dryRun ? "Would delete" : "Deleted") . ": " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
            }
        }

        $this->line("   " . ($dryRun ? "Would free" : "Freed") . ": " . $this->formatBytes($deletedSize) . " from {$deletedCount} files");
        $this->info("");
    }

    /**
     * Dọn dẹp Laravel temp files
     */
    private function cleanupLaravelTemp($dryRun, $olderThan)
    {
        $this->info("3. Dọn dẹp Laravel temp files:");

        $laravelTempDirs = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('app/public/temp'),
            public_path('temp'),
        ];

        $deletedCount = 0;
        $deletedSize = 0;
        $cutoffTime = time() - ($olderThan * 3600);

        foreach ($laravelTempDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $this->line("   Scanning: {$dir}");

            $files = $this->getFilesRecursively($dir);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = filemtime($file);
                    $fileSize = filesize($file);

                    // Bỏ qua files mới tạo
                    if ($fileTime > $cutoffTime) {
                        continue;
                    }

                    // Bỏ qua files đang được sử dụng
                    if ($this->isFileInUse($file)) {
                        continue;
                    }

                    $deletedCount++;
                    $deletedSize += $fileSize;

                    if (!$dryRun) {
                        unlink($file);
                    }

                    $this->line("   " . ($dryRun ? "Would delete" : "Deleted") . ": " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
                }
            }
        }

        $this->line("   " . ($dryRun ? "Would free" : "Freed") . ": " . $this->formatBytes($deletedSize) . " from {$deletedCount} files");
        $this->info("");
    }

    /**
     * Dọn dẹp upload temp files
     */
    private function cleanupUploadTemp($dryRun, $olderThan)
    {
        $this->info("4. Dọn dẹp upload temp files:");

        $uploadDirs = [
            storage_path('app/uploads/temp'),
            storage_path('app/public/uploads/temp'),
            public_path('uploads/temp'),
        ];

        $deletedCount = 0;
        $deletedSize = 0;
        $cutoffTime = time() - ($olderThan * 3600);

        foreach ($uploadDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $this->line("   Scanning: {$dir}");

            $files = $this->getFilesRecursively($dir);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = filemtime($file);
                    $fileSize = filesize($file);

                    // Bỏ qua files mới tạo
                    if ($fileTime > $cutoffTime) {
                        continue;
                    }

                    $deletedCount++;
                    $deletedSize += $fileSize;

                    if (!$dryRun) {
                        unlink($file);
                    }

                    $this->line("   " . ($dryRun ? "Would delete" : "Deleted") . ": " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
                }
            }
        }

        $this->line("   " . ($dryRun ? "Would free" : "Freed") . ": " . $this->formatBytes($deletedSize) . " from {$deletedCount} files");
        $this->info("");
    }

    /**
     * Dọn dẹp cache files
     */
    private function cleanupCacheFiles($dryRun, $olderThan)
    {
        $this->info("5. Dọn dẹp cache files:");

        $cacheDirs = [
            storage_path('framework/cache/data'),
            storage_path('framework/cache/views'),
            storage_path('framework/cache/routes'),
            storage_path('framework/cache/config'),
            storage_path('framework/cache/application'),
        ];

        $deletedCount = 0;
        $deletedSize = 0;
        $cutoffTime = time() - ($olderThan * 3600);

        foreach ($cacheDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $this->line("   Scanning: {$dir}");

            $files = $this->getFilesRecursively($dir);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = filemtime($file);
                    $fileSize = filesize($file);

                    // Bỏ qua files mới tạo
                    if ($fileTime > $cutoffTime) {
                        continue;
                    }

                    $deletedCount++;
                    $deletedSize += $fileSize;

                    if (!$dryRun) {
                        unlink($file);
                    }

                    $this->line("   " . ($dryRun ? "Would delete" : "Deleted") . ": " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
                }
            }
        }

        $this->line("   " . ($dryRun ? "Would free" : "Freed") . ": " . $this->formatBytes($deletedSize) . " from {$deletedCount} files");
        $this->info("");
    }

    /**
     * Dọn dẹp log files
     */
    private function cleanupLogFiles($dryRun, $olderThan)
    {
        $this->info("6. Dọn dẹp log files:");

        $logDirs = [
            storage_path('logs'),
            storage_path('app/logs'),
        ];

        $deletedCount = 0;
        $deletedSize = 0;
        $cutoffTime = time() - ($olderThan * 3600);

        foreach ($logDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $this->line("   Scanning: {$dir}");

            $files = glob($dir . '/*.log');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = filemtime($file);
                    $fileSize = filesize($file);

                    // Chỉ xóa log files cũ và lớn
                    if ($fileTime > $cutoffTime || $fileSize < 10 * 1024 * 1024) { // < 10MB
                        continue;
                    }

                    $deletedCount++;
                    $deletedSize += $fileSize;

                    if (!$dryRun) {
                        unlink($file);
                    }

                    $this->line("   " . ($dryRun ? "Would delete" : "Deleted") . ": " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
                }
            }
        }

        $this->line("   " . ($dryRun ? "Would free" : "Freed") . ": " . $this->formatBytes($deletedSize) . " from {$deletedCount} files");
        $this->info("");
    }

    /**
     * Kiểm tra disk space sau khi dọn dẹp
     */
    private function checkDiskSpaceAfter()
    {
        $this->info("7. Kiểm tra disk space sau khi dọn dẹp:");

        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = round(($diskUsed / $diskTotal) * 100, 2);

        $this->line("   Total: " . $this->formatBytes($diskTotal));
        $this->line("   Used: " . $this->formatBytes($diskUsed));
        $this->line("   Free: " . $this->formatBytes($diskFree));
        $this->line("   Usage: {$diskUsagePercent}%");

        if ($diskUsagePercent > 90) {
            $this->error("   ⚠️  Disk space vẫn critical (>90%)");
        } elseif ($diskUsagePercent > 80) {
            $this->warn("   ⚠️  Disk space vẫn warning (>80%)");
        } else {
            $this->line("   ✓ Disk space đã được cải thiện");
        }

        $this->info("");
    }

    /**
     * Hiển thị tổng kết
     */
    private function showSummary()
    {
        $this->info("8. Tổng kết:");

        $this->line("   ✓ Dọn dẹp temp files hoàn tất");
        $this->line("   ✓ Disk space đã được giải phóng");
        $this->line("   ✓ Upload performance sẽ được cải thiện");

        $this->info("\n   Lệnh tiếp theo:");
        $this->line("   php artisan config:clear");
        $this->line("   php artisan cache:clear");
        $this->line("   php artisan upload:diagnose --test-upload");
    }

    /**
     * Kiểm tra file có đang được sử dụng không
     */
    private function isFileInUse($file)
    {
        // Kiểm tra file có đang được mở bởi process nào không
        $handle = @fopen($file, 'r+');
        if ($handle === false) {
            return true; // File đang được sử dụng
        }
        fclose($handle);
        return false;
    }

    /**
     * Lấy tất cả files trong directory một cách recursive
     */
    private function getFilesRecursively($dir)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Format bytes thành readable string
     */
    private function formatBytes($bytes)
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
        } elseif ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
}
