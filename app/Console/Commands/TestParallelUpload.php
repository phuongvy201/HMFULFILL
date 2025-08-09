<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\S3MultipartUploadService;
use Illuminate\Support\Facades\Log;

class TestParallelUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:parallel-upload 
                           {--file= : Path to test file}
                           {--parallel : Enable parallel upload}
                           {--size= : Test file size in MB (will create dummy file)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test S3 multipart upload performance (parallel vs sequential)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->option('file');
        $enableParallel = $this->option('parallel');
        $testSize = $this->option('size');

        if (!$filePath && !$testSize) {
            $this->error('Please provide either --file or --size option');
            return 1;
        }

        // Create dummy file if size specified
        if ($testSize) {
            $filePath = $this->createDummyFile($testSize);
        }

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Testing S3 multipart upload...");
        $this->info("File: {$filePath}");
        $this->info("Size: " . round(filesize($filePath) / 1024 / 1024, 2) . " MB");
        $this->info("Parallel: " . ($enableParallel ? 'Yes' : 'No'));

        // Temporarily set config
        if ($enableParallel) {
            config(['multipart-upload.performance.enable_parallel' => true]);
        } else {
            config(['multipart-upload.performance.enable_parallel' => false]);
        }

        try {
            $uploadService = new S3MultipartUploadService();

            $startTime = microtime(true);

            // Create UploadedFile mock
            $uploadedFile = new \Illuminate\Http\Testing\File(
                basename($filePath),
                fopen($filePath, 'r')
            );

            $destinationPath = 'test-uploads/' . time() . '_' . basename($filePath);

            $this->info("Starting upload...");

            $result = $uploadService->uploadFile($uploadedFile, $destinationPath);

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);

            if ($result !== false) {
                $this->info("✅ Upload successful!");
                $this->info("Duration: {$duration} seconds");
                $this->info("Path: {$result}");

                // Calculate upload speed
                $fileSize = filesize($filePath);
                $speedMbps = round(($fileSize / 1024 / 1024) / $duration, 2);
                $this->info("Speed: {$speedMbps} MB/s");

                return 0;
            } else {
                $this->error("❌ Upload failed");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Upload error: " . $e->getMessage());
            Log::error('Test parallel upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        } finally {
            // Cleanup dummy file
            if ($testSize && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Create a dummy file for testing
     */
    protected function createDummyFile(int $sizeMB): string
    {
        $filePath = storage_path("app/test_file_{$sizeMB}MB.dat");

        $this->info("Creating {$sizeMB}MB dummy file...");

        $handle = fopen($filePath, 'w');
        $chunkSize = 1024 * 1024; // 1MB chunks
        $data = str_repeat('A', $chunkSize);

        for ($i = 0; $i < $sizeMB; $i++) {
            fwrite($handle, $data);
        }

        fclose($handle);

        $this->info("Dummy file created: {$filePath}");

        return $filePath;
    }
}
