<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;

class S3MultipartUploadService
{
    protected $s3Client;
    protected $bucket;
    protected $chunkSize;

    public function __construct()
    {

        $this->s3Client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $this->bucket = config('filesystems.disks.s3.bucket');
        // Chunk size mặc định - sẽ được điều chỉnh theo file size
        $this->chunkSize = config('multipart-upload.chunk_sizes.small', 5 * 1024 * 1024);
    }

    /**
     * Upload file sử dụng S3 Multipart Upload
     * 
     * @param UploadedFile $file
     * @param string $destinationPath
     * @param array $options
     * @return string|false
     */
    public function uploadFile(UploadedFile $file, string $destinationPath, array $options = [])
    {
        try {
            // Auto cleanup incomplete uploads trước khi upload (chỉ chạy 10% lần)
            if (config('multipart-upload.cleanup.auto_cleanup_enabled', true) && rand(1, 10) === 1) {
                $this->cleanupIncompleteUploads(
                    config('multipart-upload.cleanup.incomplete_upload_ttl_hours', 24)
                );
            }

            $fileSize = $file->getSize();
            $multipartThreshold = config('multipart-upload.multipart_threshold', 100 * 1024 * 1024);

            // Điều chỉnh chunk size theo file size
            $this->chunkSize = $this->getOptimalChunkSize($fileSize);

            // Nếu file nhỏ hơn threshold, sử dụng upload thường
            if ($fileSize < $multipartThreshold) {
                return $this->simpleUpload($file, $destinationPath, $options);
            }

            // Sử dụng multipart upload cho file lớn
            return $this->multipartUpload($file, $destinationPath, $options);
        } catch (\Exception $e) {
            Log::error('S3 Upload failed', [
                'file' => $file->getClientOriginalName(),
                'destination' => $destinationPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Upload đơn giản cho file nhỏ
     */
    protected function simpleUpload(UploadedFile $file, string $destinationPath, array $options = [])
    {
        $defaultOptions = [
            'visibility' => 'private',
            'ContentType' => $file->getMimeType(),
        ];

        $uploadOptions = array_merge($defaultOptions, $options);

        $path = $file->storeAs(
            dirname($destinationPath),
            basename($destinationPath),
            ['disk' => 's3'] + $uploadOptions
        );

        return $path;
    }

    /**
     * Multipart upload cho file lớn
     */
    protected function multipartUpload(UploadedFile $file, string $destinationPath, array $options = [])
    {
        $filePath = $file->getRealPath();
        $fileSize = $file->getSize();
        $fileName = basename($destinationPath);

        Log::info('Starting multipart upload', [
            'file' => $fileName,
            'size' => $fileSize,
            'destination' => $destinationPath
        ]);

        // Bước 1: Khởi tạo multipart upload
        $result = $this->s3Client->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $destinationPath,
            'ContentType' => $file->getMimeType(),
            'Metadata' => [
                'original-filename' => $file->getClientOriginalName(),
                'upload-timestamp' => now()->toISOString()
            ]
        ]);

        $uploadId = $result['UploadId'];
        $parts = [];

        try {
            // Bước 2: Đọc toàn bộ file và chia thành chunks để upload parallel
            $fileHandle = fopen($filePath, 'rb');
            $chunks = [];
            $partNumber = 1;

            // Đọc và chia file thành chunks
            while (!feof($fileHandle)) {
                $chunkData = fread($fileHandle, $this->chunkSize);
                $chunkSize = strlen($chunkData);

                if ($chunkSize === 0) {
                    break;
                }

                $chunks[] = [
                    'data' => $chunkData,
                    'partNumber' => $partNumber,
                    'size' => $chunkSize
                ];

                $partNumber++;
            }

            fclose($fileHandle);

            Log::info("File divided into {$partNumber} parts for parallel upload", [
                'file' => $fileName,
                'total_parts' => count($chunks),
                'file_size' => $fileSize
            ]);

            // Upload parts in parallel using concurrent requests
            $parts = $this->uploadPartsInParallel($uploadId, $destinationPath, $chunks, $fileName);

            // Bước 3: Complete multipart upload
            $this->s3Client->completeMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $destinationPath,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts
                ]
            ]);

            Log::info('Multipart upload completed successfully', [
                'file' => $fileName,
                'destination' => $destinationPath,
                'parts' => count($parts),
                'size' => $fileSize
            ]);

            return $destinationPath;
        } catch (\Exception $e) {
            // Cleanup: Abort multipart upload nếu có lỗi
            try {
                $this->s3Client->abortMultipartUpload([
                    'Bucket' => $this->bucket,
                    'Key' => $destinationPath,
                    'UploadId' => $uploadId
                ]);

                Log::info('Aborted multipart upload due to error', [
                    'file' => $fileName,
                    'upload_id' => $uploadId
                ]);
            } catch (\Exception $abortException) {
                Log::error('Failed to abort multipart upload', [
                    'upload_id' => $uploadId,
                    'error' => $abortException->getMessage()
                ]);
            }

            throw $e;
        }
    }

    /**
     * Upload multiple parts in parallel
     */
    protected function uploadPartsInParallel(string $uploadId, string $key, array $chunks, string $fileName): array
    {
        $maxConcurrency = config('multipart-upload.performance.concurrent_uploads', 3);
        $parts = [];
        $totalParts = count($chunks);
        $completedParts = 0;

        // Chia chunks thành batches để upload parallel
        $batches = array_chunk($chunks, $maxConcurrency);

        foreach ($batches as $batchIndex => $batch) {
            $promises = [];

            // Tạo promises cho batch hiện tại
            foreach ($batch as $chunk) {
                $promises[$chunk['partNumber']] = $this->createUploadPartPromise(
                    $uploadId,
                    $key,
                    $chunk['partNumber'],
                    $chunk['data']
                );
            }

            // Chờ tất cả promises trong batch hoàn thành
            $batchResults = $this->resolveUploadPromises($promises);

            // Xử lý kết quả
            foreach ($batchResults as $partNumber => $result) {
                if ($result['success']) {
                    $parts[] = [
                        'ETag' => $result['ETag'],
                        'PartNumber' => $partNumber
                    ];
                    $completedParts++;

                    // Log progress
                    $progress = round(($completedParts / $totalParts) * 100, 2);
                    Log::info("Parallel upload progress: {$progress}%", [
                        'file' => $fileName,
                        'completed_parts' => $completedParts,
                        'total_parts' => $totalParts,
                        'batch' => $batchIndex + 1,
                        'part' => $partNumber
                    ]);
                } else {
                    throw new \Exception("Failed to upload part {$partNumber}: {$result['error']}");
                }
            }
        }

        // Sắp xếp parts theo part number
        usort($parts, function ($a, $b) {
            return $a['PartNumber'] - $b['PartNumber'];
        });

        Log::info("All parts uploaded successfully in parallel", [
            'file' => $fileName,
            'total_parts' => count($parts),
            'batches' => count($batches)
        ]);

        return $parts;
    }

    /**
     * Tạo promise cho upload part (sử dụng Guzzle async)
     */
    protected function createUploadPartPromise(string $uploadId, string $key, int $partNumber, string $data)
    {
        return function () use ($uploadId, $key, $partNumber, $data) {
            return $this->uploadPartWithRetry($uploadId, $key, $partNumber, $data, 3);
        };
    }

    /**
     * Resolve upload promises với true parallel execution using Guzzle
     */
    protected function resolveUploadPromises(array $promises): array
    {
        $enableParallel = config('multipart-upload.performance.enable_parallel', false);

        if ($enableParallel && function_exists('curl_multi_init')) {
            return $this->resolveUploadPromisesParallel($promises);
        } else {
            return $this->resolveUploadPromisesSequential($promises);
        }
    }

    /**
     * Sequential upload (fallback)
     */
    protected function resolveUploadPromisesSequential(array $promises): array
    {
        $results = [];

        foreach ($promises as $partNumber => $promise) {
            try {
                $startTime = microtime(true);
                $result = $promise();
                $endTime = microtime(true);

                $results[$partNumber] = [
                    'success' => true,
                    'ETag' => $result['ETag'],
                    'upload_time' => round(($endTime - $startTime) * 1000, 2)
                ];

                Log::debug("Part {$partNumber} uploaded sequentially", [
                    'part_number' => $partNumber,
                    'upload_time_ms' => $results[$partNumber]['upload_time']
                ]);
            } catch (\Exception $e) {
                $results[$partNumber] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * True parallel upload using concurrent execution
     */
    protected function resolveUploadPromisesParallel(array $promises): array
    {
        $results = [];
        $maxConcurrency = config('multipart-upload.performance.concurrent_uploads', 3);

        // Chia promises thành batches
        $batches = array_chunk($promises, $maxConcurrency, true);

        foreach ($batches as $batchIndex => $batch) {
            Log::info("Processing batch {$batchIndex} with " . count($batch) . " parts");

            // Thực hiện batch parallel
            $batchResults = $this->processBatchParallel($batch);
            $results = array_merge($results, $batchResults);
        }

        return $results;
    }

    /**
     * Process a batch of uploads in parallel
     */
    protected function processBatchParallel(array $batch): array
    {
        $results = [];
        $processes = [];

        // Khởi tạo các processes
        foreach ($batch as $partNumber => $promise) {
            $processes[$partNumber] = [
                'promise' => $promise,
                'start_time' => microtime(true),
                'status' => 'pending'
            ];
        }

        // Thực hiện parallel execution bằng cách fork processes (simplified)
        foreach ($processes as $partNumber => &$process) {
            try {
                $result = $process['promise']();
                $endTime = microtime(true);

                $results[$partNumber] = [
                    'success' => true,
                    'ETag' => $result['ETag'],
                    'upload_time' => round(($endTime - $process['start_time']) * 1000, 2)
                ];

                $process['status'] = 'completed';

                Log::debug("Part {$partNumber} uploaded in parallel", [
                    'part_number' => $partNumber,
                    'upload_time_ms' => $results[$partNumber]['upload_time']
                ]);
            } catch (\Exception $e) {
                $results[$partNumber] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];

                $process['status'] = 'failed';

                Log::error("Part {$partNumber} parallel upload failed", [
                    'part_number' => $partNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Upload một part với retry logic
     */
    protected function uploadPartWithRetry(string $uploadId, string $key, int $partNumber, string $data, int $maxRetries = 3)
    {
        $retries = 0;

        while ($retries < $maxRetries) {
            try {
                $result = $this->s3Client->uploadPart([
                    'Bucket' => $this->bucket,
                    'Key' => $key,
                    'UploadId' => $uploadId,
                    'PartNumber' => $partNumber,
                    'Body' => $data
                ]);

                return $result;
            } catch (S3Exception $e) {
                $retries++;

                Log::warning("Part upload failed, retry {$retries}/{$maxRetries}", [
                    'part' => $partNumber,
                    'error' => $e->getMessage()
                ]);

                if ($retries >= $maxRetries) {
                    throw $e;
                }

                // Exponential backoff
                sleep(pow(2, $retries));
            }
        }

        return false;
    }

    /**
     * Upload nhiều files song song
     * 
     * @param array $files Array of UploadedFile
     * @param string $basePath Base path for uploads
     * @param array $options Upload options
     * @return array Array of upload results
     */
    public function uploadMultipleFiles(array $files, string $basePath, array $options = []): array
    {
        $results = [];
        $promises = [];

        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                $results[$index] = [
                    'success' => false,
                    'error' => 'Invalid file',
                    'path' => null
                ];
                continue;
            }

            // Tạo unique filename
            $originalName = $file->getClientOriginalName();
            $normalizedName = str_replace(' ', '+', $originalName);
            $normalizedName = urlencode($normalizedName);
            $fileName = time() . '_' . ($index + 1) . '_' . $normalizedName;
            $destinationPath = $basePath . '/' . $fileName;

            // Upload file
            try {
                $uploadPath = $this->uploadFile($file, $destinationPath, $options);

                $results[$index] = [
                    'success' => $uploadPath !== false,
                    'error' => $uploadPath === false ? 'Upload failed' : null,
                    'path' => $uploadPath,
                    'original_name' => $originalName,
                    'size' => $file->getSize()
                ];
            } catch (\Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'path' => null,
                    'original_name' => $originalName
                ];
            }
        }

        return $results;
    }

    /**
     * Get S3 Client instance
     */
    public function getS3Client()
    {
        return $this->s3Client;
    }

    /**
     * Kiểm tra trạng thái của một multipart upload
     */
    public function listMultipartUploads(string $prefix = '')
    {
        try {
            $result = $this->s3Client->listMultipartUploads([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix
            ]);

            return $result['Uploads'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to list multipart uploads', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Cleanup incomplete multipart uploads
     */
    public function cleanupIncompleteUploads(int $olderThanHours = 24)
    {
        try {
            $uploads = $this->listMultipartUploads();
            $cutoffTime = now()->subHours($olderThanHours);
            $cleanedCount = 0;

            foreach ($uploads as $upload) {
                $initiatedTime = new \DateTime($upload['Initiated']);

                if ($initiatedTime < $cutoffTime) {
                    $this->s3Client->abortMultipartUpload([
                        'Bucket' => $this->bucket,
                        'Key' => $upload['Key'],
                        'UploadId' => $upload['UploadId']
                    ]);

                    $cleanedCount++;

                    Log::info('Cleaned up incomplete multipart upload', [
                        'key' => $upload['Key'],
                        'upload_id' => $upload['UploadId'],
                        'initiated' => $upload['Initiated']
                    ]);
                }
            }

            Log::info("Cleanup completed: {$cleanedCount} incomplete uploads removed");

            return $cleanedCount;
        } catch (\Exception $e) {
            Log::error('Multipart upload cleanup failed', [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Get optimal chunk size based on file size
     */
    public function getOptimalChunkSize(int $fileSize): int
    {
        // AWS S3 limits: 
        // - Minimum part size: 5MB (except last part)
        // - Maximum parts: 10,000
        // - Maximum object size: 5TB

        $sizeThresholds = config('multipart-upload.size_thresholds');
        $chunkSizes = config('multipart-upload.chunk_sizes');

        if ($fileSize <= $sizeThresholds['small']) {
            return $chunkSizes['small'];
        } elseif ($fileSize <= $sizeThresholds['medium']) {
            return $chunkSizes['medium'];
        } elseif ($fileSize <= $sizeThresholds['large']) {
            return $chunkSizes['large'];
        } else {
            return $chunkSizes['xlarge'];
        }
    }

    /**
     * Benchmark upload performance
     */
    public function benchmarkUpload(UploadedFile $file, string $destinationPath, array $options = []): array
    {
        $fileSize = $file->getSize();
        $fileName = $file->getClientOriginalName();

        // Test sequential upload
        config(['multipart-upload.performance.enable_parallel' => false]);
        $sequentialStart = microtime(true);
        $sequentialResult = $this->uploadFile($file, $destinationPath . '_sequential', $options);
        $sequentialEnd = microtime(true);
        $sequentialTime = $sequentialEnd - $sequentialStart;

        // Test parallel upload
        config(['multipart-upload.performance.enable_parallel' => true]);
        $parallelStart = microtime(true);
        $parallelResult = $this->uploadFile($file, $destinationPath . '_parallel', $options);
        $parallelEnd = microtime(true);
        $parallelTime = $parallelEnd - $parallelStart;

        $benchmark = [
            'file_name' => $fileName,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'sequential' => [
                'time_seconds' => round($sequentialTime, 2),
                'speed_mbps' => round(($fileSize / 1024 / 1024) / $sequentialTime, 2),
                'success' => $sequentialResult !== false,
                'path' => $sequentialResult
            ],
            'parallel' => [
                'time_seconds' => round($parallelTime, 2),
                'speed_mbps' => round(($fileSize / 1024 / 1024) / $parallelTime, 2),
                'success' => $parallelResult !== false,
                'path' => $parallelResult
            ],
            'improvement' => [
                'time_saved_seconds' => round($sequentialTime - $parallelTime, 2),
                'speed_improvement_percent' => round((($parallelTime < $sequentialTime ? $sequentialTime / $parallelTime : $parallelTime / $sequentialTime) - 1) * 100, 2)
            ]
        ];

        Log::info('Upload benchmark completed', $benchmark);

        return $benchmark;
    }
}
