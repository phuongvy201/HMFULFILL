<?php

/**
 * Script test upload response time
 * Chạy: php test_upload_response.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Log;

class UploadResponseTester
{
    public function testUploadResponse()
    {
        echo "🧪 Test Upload Response Time\n";
        echo "============================\n\n";
        
        // Test 1: Kiểm tra PHP settings
        echo "1️⃣ Kiểm tra PHP Settings:\n";
        $settings = [
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_input_time' => ini_get('max_input_time'),
            'default_socket_timeout' => ini_get('default_socket_timeout'),
        ];
        
        foreach ($settings as $key => $value) {
            echo "  ⚙️  {$key}: {$value}\n";
        }
        
        // Test 2: Kiểm tra S3 connection
        echo "\n2️⃣ Test S3 Connection:\n";
        try {
            $s3Client = new Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);
            
            $startTime = microtime(true);
            $result = $s3Client->listBuckets();
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            echo "  ✅ S3 connection: {$responseTime}ms\n";
            echo "  📦 Buckets: " . implode(', ', array_column($result['Buckets'], 'Name')) . "\n";
            
        } catch (Exception $e) {
            echo "  ❌ S3 connection failed: " . $e->getMessage() . "\n";
        }
        
        // Test 3: Tạo test file và upload
        echo "\n3️⃣ Test File Upload:\n";
        
        // Tạo test file 5MB
        $testFile = tempnam(sys_get_temp_dir(), 'test_upload_');
        $testContent = str_repeat('A', 5 * 1024 * 1024); // 5MB
        file_put_contents($testFile, $testContent);
        
        echo "  📄 Test file created: " . basename($testFile) . " (" . $this->formatBytes(filesize($testFile)) . ")\n";
        
        try {
            $uploadService = new App\Services\S3MultipartUploadService();
            
            $startTime = microtime(true);
            
            $result = $uploadService->uploadFile(
                new Illuminate\Http\UploadedFile($testFile, 'test_upload.txt'),
                'test/response_test_' . time() . '.txt',
                [
                    'visibility' => 'private',
                    'metadata' => [
                        'test-type' => 'response-time-test',
                        'timestamp' => time()
                    ]
                ]
            );
            
            $endTime = microtime(true);
            $uploadTime = round(($endTime - $startTime) * 1000, 2);
            
            if ($result !== false) {
                echo "  ✅ Upload successful: {$uploadTime}ms\n";
                echo "  📁 Path: {$result}\n";
            } else {
                echo "  ❌ Upload failed\n";
            }
            
        } catch (Exception $e) {
            echo "  ❌ Upload error: " . $e->getMessage() . "\n";
        }
        
        // Cleanup
        unlink($testFile);
        
        // Test 4: Kiểm tra memory usage
        echo "\n4️⃣ Memory Usage:\n";
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        echo "  💾 Current: " . $this->formatBytes($memoryUsage) . "\n";
        echo "  📈 Peak: " . $this->formatBytes($memoryPeak) . "\n";
        echo "  ⚙️  Limit: {$memoryLimit}\n";
        
        // Test 5: Kiểm tra disk space
        echo "\n5️⃣ Disk Space:\n";
        $freeSpace = disk_free_space(__DIR__);
        $totalSpace = disk_total_space(__DIR__);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);
        
        echo "  💽 Used: " . $this->formatBytes($usedSpace) . " / " . $this->formatBytes($totalSpace) . " ({$usagePercent}%)\n";
        
        // Test 6: Kiểm tra temp directory
        echo "\n6️⃣ Temp Directory:\n";
        $tempDir = sys_get_temp_dir();
        $tempFiles = glob($tempDir . '/php*');
        $tempCount = count($tempFiles);
        $tempSize = 0;
        
        foreach ($tempFiles as $file) {
            if (is_file($file)) {
                $tempSize += filesize($file);
            }
        }
        
        echo "  📁 Temp dir: {$tempDir}\n";
        echo "  📄 PHP temp files: {$tempCount} (" . $this->formatBytes($tempSize) . ")\n";
        
        // Test 7: Kiểm tra Laravel logs
        echo "\n7️⃣ Laravel Logs:\n";
        $logFile = __DIR__ . '/storage/logs/laravel.log';
        
        if (file_exists($logFile)) {
            $logSize = filesize($logFile);
            $logModified = date('Y-m-d H:i:s', filemtime($logFile));
            
            echo "  📄 Log file: " . $this->formatBytes($logSize) . " - {$logModified}\n";
            
            // Tìm lỗi gần đây
            $lines = file($logFile);
            $recentLines = array_slice($lines, -5);
            
            echo "  📝 Recent entries:\n";
            foreach ($recentLines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    echo "    " . substr($line, 0, 100) . "...\n";
                }
            }
        } else {
            echo "  ❌ Log file not found\n";
        }
        
        echo "\n✅ Test completed!\n";
        
        // Recommendations
        echo "\n💡 Recommendations:\n";
        
        if (ini_get('max_execution_time') < 300) {
            echo "  ⚠️  Consider increasing max_execution_time to 300+ seconds\n";
        }
        
        if (ini_get('memory_limit') < '512M') {
            echo "  ⚠️  Consider increasing memory_limit to 512M+\n";
        }
        
        if ($usagePercent > 90) {
            echo "  ⚠️  Disk space is running low\n";
        }
        
        if ($tempCount > 100) {
            echo "  ⚠️  Many temp files found, consider cleanup\n";
        }
        
        echo "\n🔧 Quick fixes:\n";
        echo "  - Add to .htaccess: php_value max_execution_time 300\n";
        echo "  - Add to .htaccess: php_value memory_limit 512M\n";
        echo "  - Check browser timeout settings\n";
        echo "  - Monitor network connectivity\n";
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Chạy test
if (php_sapi_name() === 'cli') {
    $tester = new UploadResponseTester();
    $tester->testUploadResponse();
}



