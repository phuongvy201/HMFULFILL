<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DiagnoseUploadIssue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:diagnose 
                           {--test-upload : Test upload file thực tế}
                           {--check-logs : Kiểm tra logs gần đây}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chẩn đoán vấn đề upload đột ngột';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== CHẨN ĐOÁN VẤN ĐỀ UPLOAD ===\n");

        // 1. Kiểm tra cấu hình hiện tại
        $this->checkCurrentConfiguration();

        // 2. Kiểm tra logs gần đây
        if ($this->option('check-logs')) {
            $this->checkRecentLogs();
        }

        // 3. Kiểm tra S3 connection
        $this->checkS3Connection();

        // 4. Kiểm tra disk space
        $this->checkDiskSpace();

        // 5. Kiểm tra memory usage
        $this->checkMemoryUsage();

        // 6. Test upload nếu được yêu cầu
        if ($this->option('test-upload')) {
            $this->testActualUpload();
        }

        // 7. Đề xuất giải pháp
        $this->suggestSolutions();

        return 0;
    }

    /**
     * Kiểm tra cấu hình hiện tại
     */
    private function checkCurrentConfiguration()
    {
        $this->info("1. Kiểm tra cấu hình hiện tại:");

        // PHP Configuration
        $phpConfigs = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'max_input_time' => ini_get('max_input_time'),
            'file_uploads' => ini_get('file_uploads'),
            'upload_tmp_dir' => ini_get('upload_tmp_dir'),
        ];

        foreach ($phpConfigs as $key => $value) {
            $status = $this->getConfigStatus($key, $value);
            $this->line("   {$key}: {$value} {$status}");
        }

        // Laravel Configuration
        $this->info("\n   Laravel Config:");
        $laravelConfigs = [
            'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
            'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID') ? '✓ Set' : '✗ Missing',
            'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY') ? '✓ Set' : '✗ Missing',
            'AWS_BUCKET' => env('AWS_BUCKET'),
            'S3_MULTIPART_THRESHOLD' => env('S3_MULTIPART_THRESHOLD', '100MB'),
            'S3_CONCURRENT_UPLOADS' => env('S3_CONCURRENT_UPLOADS', '3'),
        ];

        foreach ($laravelConfigs as $key => $value) {
            $this->line("   {$key}: {$value}");
        }

        $this->info("");
    }

    /**
     * Kiểm tra logs gần đây
     */
    private function checkRecentLogs()
    {
        $this->info("2. Kiểm tra logs gần đây:");

        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            $this->warn("   Không tìm thấy log file");
            return;
        }

        // Đọc 50 dòng cuối cùng
        $lines = file($logFile);
        $recentLines = array_slice($lines, -50);

        $uploadErrors = [];
        $s3Errors = [];
        $memoryErrors = [];

        foreach ($recentLines as $line) {
            if (strpos($line, 'S3 Upload failed') !== false) {
                $uploadErrors[] = trim($line);
            }
            if (strpos($line, 'S3Exception') !== false) {
                $s3Errors[] = trim($line);
            }
            if (strpos($line, 'memory') !== false || strpos($line, 'Memory') !== false) {
                $memoryErrors[] = trim($line);
            }
        }

        if (!empty($uploadErrors)) {
            $this->error("   Upload errors found:");
            foreach (array_slice($uploadErrors, -5) as $error) {
                $this->line("     " . substr($error, 0, 100) . "...");
            }
        }

        if (!empty($s3Errors)) {
            $this->error("   S3 errors found:");
            foreach (array_slice($s3Errors, -5) as $error) {
                $this->line("     " . substr($error, 0, 100) . "...");
            }
        }

        if (!empty($memoryErrors)) {
            $this->error("   Memory errors found:");
            foreach (array_slice($memoryErrors, -5) as $error) {
                $this->line("     " . substr($error, 0, 100) . "...");
            }
        }

        if (empty($uploadErrors) && empty($s3Errors) && empty($memoryErrors)) {
            $this->line("   ✓ Không có lỗi upload gần đây");
        }

        $this->info("");
    }

    /**
     * Kiểm tra S3 connection
     */
    private function checkS3Connection()
    {
        $this->info("3. Kiểm tra S3 connection:");

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-2'),
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $bucket = config('filesystems.disks.s3.bucket');

            // Test 1: List objects
            $startTime = microtime(true);
            $result = $s3Client->listObjectsV2([
                'Bucket' => $bucket,
                'MaxKeys' => 1
            ]);
            $endTime = microtime(true);

            $responseTime = round(($endTime - $startTime) * 1000, 2);
            $this->line("   ✓ S3 connection OK ({$responseTime}ms)");

            // Test 2: Check bucket permissions
            try {
                $s3Client->headBucket(['Bucket' => $bucket]);
                $this->line("   ✓ Bucket permissions OK");
            } catch (\Exception $e) {
                $this->error("   ✗ Bucket permissions issue: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error("   ✗ S3 connection failed: " . $e->getMessage());
        }

        $this->info("");
    }

    /**
     * Kiểm tra disk space
     */
    private function checkDiskSpace()
    {
        $this->info("4. Kiểm tra disk space:");

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

        // Kiểm tra temp directory
        $tempDir = sys_get_temp_dir();
        $tempFree = disk_free_space($tempDir);
        $this->line("   Temp dir free: " . $this->formatBytes($tempFree));

        if ($tempFree < 100 * 1024 * 1024) { // < 100MB
            $this->error("   ⚠️  Temp directory low space");
        }

        $this->info("");
    }

    /**
     * Kiểm tra memory usage
     */
    private function checkMemoryUsage()
    {
        $this->info("5. Kiểm tra memory usage:");

        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        $this->line("   Memory limit: " . $memoryLimit);
        $this->line("   Current usage: " . $this->formatBytes($memoryUsage));
        $this->line("   Peak usage: " . $this->formatBytes($memoryPeak));

        // Kiểm tra memory limit
        $limitBytes = $this->parseSize($memoryLimit);
        if ($limitBytes > 0) {
            $usagePercent = round(($memoryPeak / $limitBytes) * 100, 2);
            $this->line("   Peak usage: {$usagePercent}% of limit");

            if ($usagePercent > 90) {
                $this->error("   ⚠️  Memory usage critical (>90%)");
            } elseif ($usagePercent > 80) {
                $this->warn("   ⚠️  Memory usage warning (>80%)");
            } else {
                $this->line("   ✓ Memory usage OK");
            }
        }

        $this->info("");
    }

    /**
     * Test upload thực tế
     */
    private function testActualUpload()
    {
        $this->info("6. Test upload thực tế:");

        try {
            // Tạo test file 1MB
            $testContent = str_repeat('A', 1024 * 1024);
            $testFile = tempnam(sys_get_temp_dir(), 'upload_test_');
            file_put_contents($testFile, $testContent);

            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-2'),
            ]);

            $bucket = config('filesystems.disks.s3.bucket');
            $testKey = 'test/diagnose_test_' . time() . '.txt';

            $startTime = microtime(true);
            $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $testKey,
                'Body' => $testContent
            ]);
            $endTime = microtime(true);

            $uploadTime = round(($endTime - $startTime) * 1000, 2);
            $this->line("   ✓ Upload 1MB successful ({$uploadTime}ms)");

            // Cleanup
            $s3Client->deleteObject(['Bucket' => $bucket, 'Key' => $testKey]);
            unlink($testFile);
        } catch (\Exception $e) {
            $this->error("   ✗ Upload test failed: " . $e->getMessage());
        }

        $this->info("");
    }

    /**
     * Đề xuất giải pháp
     */
    private function suggestSolutions()
    {
        $this->info("7. Đề xuất giải pháp:");

        $solutions = [];

        // Kiểm tra các vấn đề phổ biến
        $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
        $postMax = $this->parseSize(ini_get('post_max_size'));
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));

        if ($uploadMax < 100 * 1024 * 1024) {
            $solutions[] = "Tăng upload_max_filesize lên 200M";
        }

        if ($postMax < 100 * 1024 * 1024) {
            $solutions[] = "Tăng post_max_size lên 200M";
        }

        if ($memoryLimit < 512 * 1024 * 1024) {
            $solutions[] = "Tăng memory_limit lên 512M";
        }

        // Kiểm tra disk space
        $diskFree = disk_free_space(storage_path());
        if ($diskFree < 500 * 1024 * 1024) { // < 500MB
            $solutions[] = "Dọn dẹp disk space (xóa logs, cache)";
        }

        // Kiểm tra temp directory
        $tempFree = disk_free_space(sys_get_temp_dir());
        if ($tempFree < 100 * 1024 * 1024) { // < 100MB
            $solutions[] = "Dọn dẹp temp directory";
        }

        if (empty($solutions)) {
            $solutions[] = "Kiểm tra network connectivity";
            $solutions[] = "Restart web server";
            $solutions[] = "Kiểm tra AWS credentials";
        }

        foreach ($solutions as $solution) {
            $this->line("   • {$solution}");
        }

        // Hiển thị lệnh cụ thể
        $this->info("\n   Lệnh khắc phục nhanh:");
        $this->line("   php artisan config:clear");
        $this->line("   php artisan cache:clear");
        $this->line("   sudo systemctl restart apache2  # hoặc nginx");
        $this->line("   php artisan upload:diagnose --test-upload");
    }

    /**
     * Lấy trạng thái của config
     */
    private function getConfigStatus($key, $value)
    {
        switch ($key) {
            case 'upload_max_filesize':
                $size = $this->parseSize($value);
                if ($size >= 200 * 1024 * 1024) return '✓';
                if ($size >= 100 * 1024 * 1024) return '⚠️';
                return '✗';

            case 'post_max_size':
                $size = $this->parseSize($value);
                if ($size >= 200 * 1024 * 1024) return '✓';
                if ($size >= 100 * 1024 * 1024) return '⚠️';
                return '✗';

            case 'memory_limit':
                $size = $this->parseSize($value);
                if ($size >= 512 * 1024 * 1024) return '✓';
                if ($size >= 256 * 1024 * 1024) return '⚠️';
                return '✗';

            case 'max_execution_time':
                if ($value >= 300) return '✓';
                if ($value >= 120) return '⚠️';
                return '✗';

            case 'file_uploads':
                return $value == '1' ? '✓' : '✗';

            default:
                return '';
        }
    }

    /**
     * Parse size string thành bytes
     */
    private function parseSize($size)
    {
        if ($size == -1) return -1; // Unlimited

        $unit = strtolower(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        switch ($unit) {
            case 'k':
                return $value * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'g':
                return $value * 1024 * 1024 * 1024;
            default:
                return $value;
        }
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
