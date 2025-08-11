<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeUploadLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:analyze-logs 
                           {--hours=24 : Phân tích logs trong X giờ gần đây}
                           {--file-size=10 : Chỉ phân tích files lớn hơn X MB}
                           {--export : Xuất kết quả ra file CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Phân tích logs upload để tìm vấn đề với file lớn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== PHÂN TÍCH UPLOAD LOGS ===\n");

        $hours = (int) $this->option('hours');
        $minFileSize = (int) $this->option('file-size');
        $export = $this->option('export');

        // 1. Đọc và parse logs
        $uploads = $this->parseUploadLogs($hours, $minFileSize);

        if (empty($uploads)) {
            $this->warn("Không tìm thấy upload logs trong {$hours} giờ gần đây");
            return 0;
        }

        // 2. Phân tích performance
        $this->analyzePerformance($uploads);

        // 3. Phân tích errors
        $this->analyzeErrors($uploads);

        // 4. So sánh file nhỏ vs file lớn
        $this->compareFileSizes($uploads);

        // 5. Tìm bottlenecks
        $this->findBottlenecks($uploads);

        // 6. Xuất kết quả nếu được yêu cầu
        if ($export) {
            $this->exportResults($uploads);
        }

        return 0;
    }

    /**
     * Parse upload logs
     */
    private function parseUploadLogs(int $hours, int $minFileSize): array
    {
        $this->info("1. Đọc upload logs trong {$hours} giờ gần đây:");

        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            $this->error("Không tìm thấy log file");
            return [];
        }

        $cutoffTime = time() - ($hours * 3600);
        $uploads = [];
        $lines = file($logFile);
        $uploadCount = 0;

        foreach ($lines as $line) {
            // Tìm các log entries liên quan đến upload
            if (
                strpos($line, 'Starting S3 upload') !== false ||
                strpos($line, 'Simple upload') !== false ||
                strpos($line, 'Multipart upload') !== false ||
                strpos($line, 'S3 Upload failed') !== false
            ) {

                $timestamp = $this->extractTimestamp($line);
                if ($timestamp && $timestamp >= $cutoffTime) {
                    $uploadData = $this->parseUploadLine($line);
                    if ($uploadData && $uploadData['file_size_mb'] >= $minFileSize) {
                        $uploads[] = $uploadData;
                        $uploadCount++;
                    }
                }
            }
        }

        $this->line("   Tìm thấy {$uploadCount} uploads");
        $this->info("");

        return $uploads;
    }

    /**
     * Phân tích performance
     */
    private function analyzePerformance(array $uploads)
    {
        $this->info("2. Phân tích Performance:");

        if (empty($uploads)) {
            $this->warn("   Không có dữ liệu để phân tích");
            return;
        }

        // Tính toán thống kê
        $successfulUploads = array_filter($uploads, fn($u) => $u['success'] ?? false);
        $failedUploads = array_filter($uploads, fn($u) => !($u['success'] ?? false));

        $this->line("   Tổng uploads: " . count($uploads));
        $this->line("   Thành công: " . count($successfulUploads));
        $this->line("   Thất bại: " . count($failedUploads));
        $this->line("   Tỷ lệ thành công: " . round((count($successfulUploads) / count($uploads)) * 100, 2) . "%");

        if (!empty($successfulUploads)) {
            $speeds = array_column($successfulUploads, 'speed_mbps');
            $times = array_column($successfulUploads, 'upload_time_ms');
            $sizes = array_column($successfulUploads, 'file_size_mb');

            $this->line("   Tốc độ upload trung bình: " . round(array_sum($speeds) / count($speeds), 2) . " MB/s");
            $this->line("   Thời gian upload trung bình: " . round(array_sum($times) / count($times), 2) . "ms");
            $this->line("   Kích thước file trung bình: " . round(array_sum($sizes) / count($sizes), 2) . "MB");
        }

        $this->info("");
    }

    /**
     * Phân tích errors
     */
    private function analyzeErrors(array $uploads)
    {
        $this->info("3. Phân tích Errors:");

        $failedUploads = array_filter($uploads, fn($u) => !($u['success'] ?? false));

        if (empty($failedUploads)) {
            $this->line("   ✓ Không có lỗi upload");
            $this->info("");
            return;
        }

        $errors = [];
        foreach ($failedUploads as $upload) {
            $error = $upload['error'] ?? 'Unknown error';
            $errors[$error] = ($errors[$error] ?? 0) + 1;
        }

        arsort($errors);

        $this->line("   Các lỗi phổ biến:");
        foreach ($errors as $error => $count) {
            $this->line("     • {$error}: {$count} lần");
        }

        $this->info("");
    }

    /**
     * So sánh file nhỏ vs file lớn
     */
    private function compareFileSizes(array $uploads)
    {
        $this->info("4. So sánh File nhỏ vs File lớn:");

        if (empty($uploads)) {
            $this->warn("   Không có dữ liệu để so sánh");
            return;
        }

        // Phân loại theo kích thước
        $smallFiles = array_filter($uploads, fn($u) => ($u['file_size_mb'] ?? 0) < 10);
        $mediumFiles = array_filter($uploads, fn($u) => ($u['file_size_mb'] ?? 0) >= 10 && ($u['file_size_mb'] ?? 0) < 50);
        $largeFiles = array_filter($uploads, fn($u) => ($u['file_size_mb'] ?? 0) >= 50);

        $this->line("   File nhỏ (< 10MB): " . count($smallFiles) . " uploads");
        $this->line("   File trung bình (10-50MB): " . count($mediumFiles) . " uploads");
        $this->line("   File lớn (> 50MB): " . count($largeFiles) . " uploads");

        // So sánh performance
        if (!empty($smallFiles)) {
            $smallSpeeds = array_column($smallFiles, 'speed_mbps');
            $smallTimes = array_column($smallFiles, 'upload_time_ms');
            $this->line("   File nhỏ - Tốc độ TB: " . round(array_sum($smallSpeeds) / count($smallSpeeds), 2) . " MB/s");
            $this->line("   File nhỏ - Thời gian TB: " . round(array_sum($smallTimes) / count($smallTimes), 2) . "ms");
        }

        if (!empty($largeFiles)) {
            $largeSpeeds = array_column($largeFiles, 'speed_mbps');
            $largeTimes = array_column($largeFiles, 'upload_time_ms');
            $this->line("   File lớn - Tốc độ TB: " . round(array_sum($largeSpeeds) / count($largeSpeeds), 2) . " MB/s");
            $this->line("   File lớn - Thời gian TB: " . round(array_sum($largeTimes) / count($largeTimes), 2) . "ms");
        }

        $this->info("");
    }

    /**
     * Tìm bottlenecks
     */
    private function findBottlenecks(array $uploads)
    {
        $this->info("5. Tìm Bottlenecks:");

        if (empty($uploads)) {
            $this->warn("   Không có dữ liệu để phân tích");
            return;
        }

        // Tìm uploads chậm nhất
        $slowUploads = array_filter($uploads, fn($u) => ($u['speed_mbps'] ?? 0) < 1);
        if (!empty($slowUploads)) {
            $this->line("   Uploads chậm (< 1 MB/s): " . count($slowUploads));

            usort($slowUploads, fn($a, $b) => ($a['speed_mbps'] ?? 0) <=> ($b['speed_mbps'] ?? 0));

            $this->line("   Top 5 uploads chậm nhất:");
            foreach (array_slice($slowUploads, 0, 5) as $upload) {
                $this->line("     • {$upload['file_name']}: {$upload['speed_mbps']} MB/s ({$upload['file_size_mb']}MB)");
            }
        }

        // Tìm uploads mất nhiều thời gian nhất
        $longUploads = array_filter($uploads, fn($u) => ($u['upload_time_ms'] ?? 0) > 30000); // > 30s
        if (!empty($longUploads)) {
            $this->line("   Uploads mất nhiều thời gian (> 30s): " . count($longUploads));

            usort($longUploads, fn($a, $b) => ($b['upload_time_ms'] ?? 0) <=> ($a['upload_time_ms'] ?? 0));

            $this->line("   Top 5 uploads lâu nhất:");
            foreach (array_slice($longUploads, 0, 5) as $upload) {
                $timeSeconds = round(($upload['upload_time_ms'] ?? 0) / 1000, 2);
                $this->line("     • {$upload['file_name']}: {$timeSeconds}s ({$upload['file_size_mb']}MB)");
            }
        }

        // Phân tích multipart upload performance
        $multipartUploads = array_filter($uploads, fn($u) => ($u['upload_method'] ?? '') === 'multipart');
        if (!empty($multipartUploads)) {
            $this->line("   Multipart uploads: " . count($multipartUploads));

            $initTimes = array_column($multipartUploads, 'init_time_ms');
            $readTimes = array_column($multipartUploads, 'read_time_ms');
            $uploadTimes = array_column($multipartUploads, 'upload_time_ms');
            $completeTimes = array_column($multipartUploads, 'complete_time_ms');

            if (!empty($initTimes)) {
                $this->line("   Init time TB: " . round(array_sum($initTimes) / count($initTimes), 2) . "ms");
            }
            if (!empty($readTimes)) {
                $this->line("   Read time TB: " . round(array_sum($readTimes) / count($readTimes), 2) . "ms");
            }
            if (!empty($uploadTimes)) {
                $this->line("   Upload time TB: " . round(array_sum($uploadTimes) / count($uploadTimes), 2) . "ms");
            }
            if (!empty($completeTimes)) {
                $this->line("   Complete time TB: " . round(array_sum($completeTimes) / count($completeTimes), 2) . "ms");
            }
        }

        $this->info("");
    }

    /**
     * Xuất kết quả ra CSV
     */
    private function exportResults(array $uploads)
    {
        $this->info("6. Xuất kết quả ra CSV:");

        $filename = 'upload_analysis_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('logs/' . $filename);

        $handle = fopen($filepath, 'w');

        // Header
        fputcsv($handle, [
            'Timestamp',
            'File Name',
            'File Size (MB)',
            'Upload Method',
            'Success',
            'Upload Time (ms)',
            'Speed (MB/s)',
            'Error',
            'Init Time (ms)',
            'Read Time (ms)',
            'Upload Time (ms)',
            'Complete Time (ms)'
        ]);

        // Data
        foreach ($uploads as $upload) {
            fputcsv($handle, [
                $upload['timestamp'] ?? '',
                $upload['file_name'] ?? '',
                $upload['file_size_mb'] ?? '',
                $upload['upload_method'] ?? '',
                $upload['success'] ? 'Yes' : 'No',
                $upload['upload_time_ms'] ?? '',
                $upload['speed_mbps'] ?? '',
                $upload['error'] ?? '',
                $upload['init_time_ms'] ?? '',
                $upload['read_time_ms'] ?? '',
                $upload['upload_time_ms'] ?? '',
                $upload['complete_time_ms'] ?? ''
            ]);
        }

        fclose($handle);

        $this->line("   Đã xuất ra: {$filepath}");
        $this->info("");
    }

    /**
     * Extract timestamp from log line
     */
    private function extractTimestamp(string $line): ?int
    {
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            return strtotime($matches[1]);
        }
        return null;
    }

    /**
     * Parse upload line
     */
    private function parseUploadLine(string $line): ?array
    {
        $data = [];

        // Extract basic info
        if (preg_match('/"file_name":"([^"]+)"/', $line, $matches)) {
            $data['file_name'] = $matches[1];
        }

        if (preg_match('/"file_size_mb":([\d.]+)/', $line, $matches)) {
            $data['file_size_mb'] = (float) $matches[1];
        }

        if (preg_match('/"upload_time_ms":([\d.]+)/', $line, $matches)) {
            $data['upload_time_ms'] = (float) $matches[1];
        }

        if (preg_match('/"speed_mbps":([\d.]+)/', $line, $matches)) {
            $data['speed_mbps'] = (float) $matches[1];
        }

        if (preg_match('/"success":(true|false)/', $line, $matches)) {
            $data['success'] = $matches[1] === 'true';
        }

        if (preg_match('/"error":"([^"]+)"/', $line, $matches)) {
            $data['error'] = $matches[1];
        }

        // Extract multipart specific info
        if (preg_match('/"init_time_ms":([\d.]+)/', $line, $matches)) {
            $data['init_time_ms'] = (float) $matches[1];
            $data['upload_method'] = 'multipart';
        }

        if (preg_match('/"read_time_ms":([\d.]+)/', $line, $matches)) {
            $data['read_time_ms'] = (float) $matches[1];
        }

        if (preg_match('/"complete_time_ms":([\d.]+)/', $line, $matches)) {
            $data['complete_time_ms'] = (float) $matches[1];
        }

        // Determine upload method
        if (strpos($line, 'Using simple upload') !== false) {
            $data['upload_method'] = 'simple';
        } elseif (strpos($line, 'Using multipart upload') !== false) {
            $data['upload_method'] = 'multipart';
        }

        return !empty($data) ? $data : null;
    }
}
