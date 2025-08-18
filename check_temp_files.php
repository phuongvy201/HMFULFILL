<?php

/**
 * Script kiểm tra file tạm thời trước khi upload S3
 * Chạy: php check_temp_files.php
 */

require_once 'vendor/autoload.php';

class TempFileChecker
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
     * Kiểm tra upload temp files
     */
    public function checkUploadTempFiles()
    {
        echo "🔍 Kiểm tra Upload Temp Files:\n";

        // Tìm tất cả files có thể là upload temp
        $patterns = [
            $this->systemTempDir . '/php*',
            $this->laravelStoragePath . '/app/temp/*',
            $this->laravelStoragePath . '/app/uploads/temp/*',
            $this->laravelStoragePath . '/app/public/uploads/temp/*',
        ];

        $uploadFiles = [];

        foreach ($patterns as $pattern) {
            $files = glob($pattern);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $uploadFiles[] = $file;
                }
            }
        }

        // Sắp xếp theo thời gian sửa đổi (mới nhất trước)
        usort($uploadFiles, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        echo "📊 Tìm thấy " . count($uploadFiles) . " files có thể là upload temp\n";

        // Hiển thị 10 files mới nhất
        $recentFiles = array_slice($uploadFiles, 0, 10);

        foreach ($recentFiles as $file) {
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            $filename = basename($file);
            $relativePath = str_replace($this->laravelStoragePath, 'storage', $file);

            echo "  📄 {$relativePath} ({$this->formatBytes($size)}) - {$modified}\n";
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
        ];

        foreach ($configs as $key => $value) {
            echo "⚙️  {$key}: {$value}\n";
        }

        echo "\n";
    }

    /**
     * Chạy tất cả kiểm tra
     */
    public function runAllChecks()
    {
        echo "🚀 Bắt đầu kiểm tra file tạm thời...\n";
        echo "=====================================\n\n";

        $this->checkSystemTemp();
        $this->checkLaravelStorage();
        $this->checkUploadTempFiles();
        $this->checkPermissions();
        $this->createTestFile();
        $this->checkUploadConfig();

        echo "✅ Hoàn thành kiểm tra!\n";
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
    $checker = new TempFileChecker();
    $checker->runAllChecks();
}

