<?php

/**
 * Script kiểm tra upload issues trên server Linux
 * Chạy: php check_upload_issues.php
 */

require_once 'vendor/autoload.php';

class UploadIssueChecker
{
    private $systemTempDir;
    private $laravelStoragePath;

    public function __construct()
    {
        $this->systemTempDir = sys_get_temp_dir();
        $this->laravelStoragePath = __DIR__ . '/storage';
    }

    /**
     * Kiểm tra system temp directory
     */
    public function checkSystemTemp()
    {
        echo "🔍 Kiểm tra System Temp Directory:\n";
        echo "Vị trí: {$this->systemTempDir}\n";

        if (!is_dir($this->systemTempDir)) {
            echo "❌ Thư mục không tồn tại!\n";
            return;
        }

        $files = glob($this->systemTempDir . '/*');
        $totalFiles = count($files);
        $totalSize = 0;
        $phpFiles = 0;
        $phpSize = 0;

        echo "📊 Tổng số files: {$totalFiles}\n";

        // Lấy 10 files mới nhất
        $recentFiles = array_slice($files, -10);

        foreach ($recentFiles as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $totalSize += $size;
                $modified = date('Y-m-d H:i:s', filemtime($file));
                $filename = basename($file);

                // Kiểm tra PHP temp files
                if (strpos($filename, 'php') === 0) {
                    $phpFiles++;
                    $phpSize += $size;
                }

                echo "  📄 {$filename} ({$this->formatBytes($size)}) - {$modified}\n";
            }
        }

        echo "📈 Tổng kích thước: {$this->formatBytes($totalSize)}\n";
        echo "🔧 PHP temp files: {$phpFiles} files ({$this->formatBytes($phpSize)})\n";

        // Kiểm tra disk space
        $freeSpace = disk_free_space($this->systemTempDir);
        $totalSpace = disk_total_space($this->systemTempDir);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);

        echo "💾 Disk space: {$this->formatBytes($usedSpace)} / {$this->formatBytes($totalSpace)} ({$usagePercent}%)\n";

        if ($usagePercent > 90) {
            echo "⚠️  Cảnh báo: Disk space sắp đầy!\n";
        } elseif ($usagePercent > 80) {
            echo "⚠️  Chú ý: Disk space đang cao\n";
        } else {
            echo "✅ Disk space OK\n";
        }

        echo "\n";
    }

    /**
     * Kiểm tra Laravel storage
     */
    public function checkLaravelStorage()
    {
        echo "🔍 Kiểm tra Laravel Storage:\n";

        $storageDirs = [
            'app/temp' => $this->laravelStoragePath . '/app/temp',
            'app/public' => $this->laravelStoragePath . '/app/public',
            'app/uploads/temp' => $this->laravelStoragePath . '/app/uploads/temp',
            'app/public/uploads/temp' => $this->laravelStoragePath . '/app/public/uploads/temp',
        ];

        foreach ($storageDirs as $name => $path) {
            echo "📁 {$name}: ";

            if (!is_dir($path)) {
                echo "❌ Không tồn tại\n";
                continue;
            }

            $files = glob($path . '/*');
            $fileCount = count($files);
            $totalSize = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                }
            }

            echo "✅ {$fileCount} files ({$this->formatBytes($totalSize)})\n";

            // Hiển thị 5 files mới nhất
            if ($fileCount > 0) {
                $recentFiles = array_slice($files, -5);
                foreach ($recentFiles as $file) {
                    if (is_file($file)) {
                        $size = filesize($file);
                        $modified = date('Y-m-d H:i:s', filemtime($file));
                        $filename = basename($file);
                        echo "    📄 {$filename} ({$this->formatBytes($size)}) - {$modified}\n";
                    }
                }
            }
        }

        echo "\n";
    }

    /**
     * Kiểm tra upload configuration
     */
    public function checkUploadConfig()
    {
        echo "🔍 Kiểm tra Upload Configuration:\n";

        $configs = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'file_uploads' => ini_get('file_uploads'),
        ];

        foreach ($configs as $key => $value) {
            echo "⚙️  {$key}: {$value}\n";
        }

        // Kiểm tra S3 configuration
        echo "\n🔍 Kiểm tra S3 Configuration:\n";

        $s3Configs = [
            'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'),
            'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'),
            'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
            'AWS_BUCKET' => env('AWS_BUCKET'),
            'AWS_URL' => env('AWS_URL'),
        ];

        foreach ($s3Configs as $key => $value) {
            $status = $value ? '✅' : '❌';
            $displayValue = $key === 'AWS_SECRET_ACCESS_KEY' ? str_repeat('*', 8) : $value;
            echo "{$status} {$key}: {$displayValue}\n";
        }

        echo "\n";
    }

    /**
     * Kiểm tra file permissions
     */
    public function checkPermissions()
    {
        echo "🔍 Kiểm tra File Permissions:\n";

        $testDirs = [
            'System Temp' => $this->systemTempDir,
            'Laravel Storage' => $this->laravelStoragePath,
            'App Temp' => $this->laravelStoragePath . '/app/temp',
            'Storage Logs' => $this->laravelStoragePath . '/logs',
        ];

        foreach ($testDirs as $name => $path) {
            echo "📁 {$name} ({$path}): ";

            if (!is_dir($path)) {
                echo "❌ Không tồn tại\n";
                continue;
            }

            $readable = is_readable($path) ? '✅' : '❌';
            $writable = is_writable($path) ? '✅' : '❌';
            $executable = is_executable($path) ? '✅' : '❌';

            echo "R:{$readable} W:{$writable} X:{$executable}\n";
        }

        echo "\n";
    }

    /**
     * Kiểm tra Laravel logs
     */
    public function checkLaravelLogs()
    {
        echo "🔍 Kiểm tra Laravel Logs:\n";

        $logFile = $this->laravelStoragePath . '/logs/laravel.log';

        if (!file_exists($logFile)) {
            echo "❌ Log file không tồn tại: {$logFile}\n";
            return;
        }

        $logSize = filesize($logFile);
        $logModified = date('Y-m-d H:i:s', filemtime($logFile));

        echo "📄 Laravel log: {$this->formatBytes($logSize)} - {$logModified}\n";

        // Đọc 10 dòng cuối cùng
        $lines = file($logFile);
        $recentLines = array_slice($lines, -10);

        echo "📝 10 dòng log gần nhất:\n";
        foreach ($recentLines as $line) {
            echo "  " . trim($line) . "\n";
        }

        echo "\n";
    }

    /**
     * Kiểm tra S3 connection
     */
    public function checkS3Connection()
    {
        echo "🔍 Kiểm tra S3 Connection:\n";

        try {
            $s3Client = new Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            // Test connection bằng cách list buckets
            $result = $s3Client->listBuckets();
            echo "✅ S3 connection thành công\n";
            echo "📦 Buckets: " . implode(', ', array_column($result['Buckets'], 'Name')) . "\n";
        } catch (Exception $e) {
            echo "❌ S3 connection failed: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    /**
     * Tạo test file để kiểm tra upload
     */
    public function createTestFile()
    {
        echo "🧪 Tạo Test File:\n";

        // Tạo test file 1MB
        $testContent = str_repeat('A', 1024 * 1024);
        $testFile = tempnam($this->systemTempDir, 'test_upload_');
        file_put_contents($testFile, $testContent);

        echo "✅ Đã tạo test file: {$testFile}\n";
        echo "📊 Kích thước: {$this->formatBytes(filesize($testFile))}\n";

        // Kiểm tra file có thể đọc được không
        if (is_readable($testFile)) {
            echo "✅ File có thể đọc được\n";
        } else {
            echo "❌ File không thể đọc được\n";
        }

        // Cleanup
        unlink($testFile);
        echo "🗑️  Đã xóa test file\n";

        echo "\n";
    }

    /**
     * Kiểm tra PHP extensions
     */
    public function checkPhpExtensions()
    {
        echo "🔍 Kiểm tra PHP Extensions:\n";

        $requiredExtensions = [
            'curl',
            'fileinfo',
            'openssl',
            'json',
            'mbstring',
            'xml',
            'zip',
        ];

        foreach ($requiredExtensions as $ext) {
            $status = extension_loaded($ext) ? '✅' : '❌';
            echo "{$status} {$ext}\n";
        }

        echo "\n";
    }

    /**
     * Kiểm tra system resources
     */
    public function checkSystemResources()
    {
        echo "🔍 Kiểm tra System Resources:\n";

        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');

        echo "💾 Memory usage: {$this->formatBytes($memoryUsage)}\n";
        echo "📈 Peak memory: {$this->formatBytes($memoryPeak)}\n";
        echo "⚙️  Memory limit: {$memoryLimit}\n";

        // CPU load
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            echo "🖥️  CPU load: {$load[0]} {$load[1]} {$load[2]}\n";
        }

        // Disk space
        $freeSpace = disk_free_space(__DIR__);
        $totalSpace = disk_total_space(__DIR__);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);

        echo "💽 Disk usage: {$this->formatBytes($usedSpace)} / {$this->formatBytes($totalSpace)} ({$usagePercent}%)\n";

        echo "\n";
    }

    /**
     * Chạy tất cả kiểm tra
     */
    public function runAllChecks()
    {
        echo "🚀 Bắt đầu kiểm tra upload issues...\n";
        echo "=====================================\n\n";

        $this->checkSystemResources();
        $this->checkPhpExtensions();
        $this->checkUploadConfig();
        $this->checkPermissions();
        $this->checkSystemTemp();
        $this->checkLaravelStorage();
        $this->checkLaravelLogs();
        $this->checkS3Connection();
        $this->createTestFile();

        echo "✅ Hoàn thành kiểm tra!\n";
        echo "\n💡 Gợi ý:\n";
        echo "- Kiểm tra logs: tail -f storage/logs/laravel.log\n";
        echo "- Cleanup temp: php artisan cleanup:temp-files --older-than=24\n";
        echo "- Test upload: php test_upload_structure.php\n";
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Chạy script
if (php_sapi_name() === 'cli') {
    $checker = new UploadIssueChecker();
    $checker->runAllChecks();
}

