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
            'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-2'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'timeout' => env('S3_UPLOAD_TIME_LIMIT', 60),
            'connect_timeout' => 30,
            'http' => [
                'timeout' => env('S3_UPLOAD_TIME_LIMIT', 60),
                'connect_timeout' => 30,
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
        $startTime = microtime(true);
        $fileSize = $file->getSize();
        $fileName = $file->getClientOriginalName();

        Log::info('Starting S3 upload', [
            'file_name' => $fileName,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'destination' => $destinationPath,
            'upload_method' => 'determining'
        ]);

        try {
            // Auto cleanup incomplete uploads trước khi upload (chỉ chạy 10% lần)
            if (config('multipart-upload.cleanup.auto_cleanup_enabled', true) && rand(1, 10) === 1) {
                Log::info('Running auto cleanup before upload');
                $this->cleanupIncompleteUploads(
                    config('multipart-upload.cleanup.incomplete_upload_ttl_hours', 24)
                );
            }

            $multipartThreshold = config('multipart-upload.multipart_threshold', 100 * 1024 * 1024);
            $this->chunkSize = $this->getOptimalChunkSize($fileSize);

            Log::info('Upload configuration', [
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'multipart_threshold_mb' => round($multipartThreshold / 1024 / 1024, 2),
                'chunk_size_mb' => round($this->chunkSize / 1024 / 1024, 2),
                'will_use_multipart' => $fileSize >= $multipartThreshold
            ]);

            // Nếu file nhỏ hơn threshold, sử dụng upload thường
            if ($fileSize < $multipartThreshold) {
                Log::info('Using simple upload for small file');
                $result = $this->simpleUpload($file, $destinationPath, $options);

                $endTime = microtime(true);
                $uploadTime = round(($endTime - $startTime) * 1000, 2);

                Log::info('Simple upload completed', [
                    'file_name' => $fileName,
                    'upload_time_ms' => $uploadTime,
                    'speed_mbps' => round(($fileSize / 1024 / 1024) / (($endTime - $startTime)), 2),
                    'success' => $result !== false
                ]);

                return $result;
            }

            // Sử dụng multipart upload cho file lớn
            Log::info('Using multipart upload for large file');
            $result = $this->multipartUpload($file, $destinationPath, $options);

            $endTime = microtime(true);
            $uploadTime = round(($endTime - $startTime) * 1000, 2);

            Log::info('Multipart upload completed', [
                'file_name' => $fileName,
                'upload_time_ms' => $uploadTime,
                'speed_mbps' => round(($fileSize / 1024 / 1024) / (($endTime - $startTime)), 2),
                'success' => $result !== false
            ]);

            return $result;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $uploadTime = round(($endTime - $startTime) * 1000, 2);

            Log::error('S3 Upload failed', [
                'file_name' => $fileName,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'destination' => $destinationPath,
                'upload_time_ms' => $uploadTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
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
        $startTime = microtime(true);
        $fileSize = $file->getSize();
        $fileName = $file->getClientOriginalName();

        Log::info('Starting simple upload', [
            'file_name' => $fileName,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'destination' => $destinationPath
        ]);

        try {
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

            $endTime = microtime(true);
            $uploadTime = round(($endTime - $startTime) * 1000, 2);

            Log::info('Simple upload successful', [
                'file_name' => $fileName,
                'upload_time_ms' => $uploadTime,
                'speed_mbps' => round(($fileSize / 1024 / 1024) / (($endTime - $startTime)), 2),
                'path' => $path
            ]);

            return $path;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $uploadTime = round(($endTime - $startTime) * 1000, 2);

            Log::error('Simple upload failed', [
                'file_name' => $fileName,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'upload_time_ms' => $uploadTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            throw $e;
        }
    }

    /**
     * Multipart upload cho file lớn
     */
    protected function multipartUpload(UploadedFile $file, string $destinationPath, array $options = [])
    {
        $startTime = microtime(true);
        $filePath = $file->getRealPath();
        $fileSize = $file->getSize();
        $fileName = basename($destinationPath);
        $originalFileName = $file->getClientOriginalName();

        Log::info('Starting multipart upload', [
            'file_name' => $originalFileName,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'destination' => $destinationPath,
            'chunk_size_mb' => round($this->chunkSize / 1024 / 1024, 2)
        ]);

        // Bước 1: Khởi tạo multipart upload
        $initStartTime = microtime(true);
        $result = $this->s3Client->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $destinationPath,
            'ContentType' => $file->getMimeType(),
            'Metadata' => [
                'original-filename' => $originalFileName,
                'upload-timestamp' => now()->toISOString()
            ]
        ]);

        $uploadId = $result['UploadId'];
        $initTime = round((microtime(true) - $initStartTime) * 1000, 2);

        Log::info('Multipart upload initialized', [
            'file_name' => $originalFileName,
            'upload_id' => $uploadId,
            'init_time_ms' => $initTime
        ]);

        $parts = [];

        try {
            // Bước 2: Đọc toàn bộ file và chia thành chunks
            $readStartTime = microtime(true);
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
            $readTime = round((microtime(true) - $readStartTime) * 1000, 2);

            Log::info('File divided into chunks', [
                'file_name' => $originalFileName,
                'total_parts' => count($chunks),
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'read_time_ms' => $readTime,
                'average_chunk_size_mb' => round($fileSize / count($chunks) / 1024 / 1024, 2)
            ]);

            // Upload parts in parallel
            $uploadStartTime = microtime(true);
            $parts = $this->uploadPartsInParallel($uploadId, $destinationPath, $chunks, $originalFileName);
            $uploadTime = round((microtime(true) - $uploadStartTime) * 1000, 2);

            // Bước 3: Complete multipart upload
            $completeStartTime = microtime(true);
            $this->s3Client->completeMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $destinationPath,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts
                ]
            ]);
            $completeTime = round((microtime(true) - $completeStartTime) * 1000, 2);

            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Multipart upload completed successfully', [
                'file_name' => $originalFileName,
                'destination' => $destinationPath,
                'total_parts' => count($parts),
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'total_time_ms' => $totalTime,
                'init_time_ms' => $initTime,
                'read_time_ms' => $readTime,
                'upload_time_ms' => $uploadTime,
                'complete_time_ms' => $completeTime,
                'speed_mbps' => round(($fileSize / 1024 / 1024) / (($totalTime / 1000)), 2)
            ]);

            return $destinationPath;
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Multipart upload failed', [
                'file_name' => $originalFileName,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'upload_id' => $uploadId,
                'total_time_ms' => $totalTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            // Cleanup: Abort multipart upload nếu có lỗi
            try {
                $this->s3Client->abortMultipartUpload([
                    'Bucket' => $this->bucket,
                    'Key' => $destinationPath,
                    'UploadId' => $uploadId
                ]);

                Log::info('Aborted multipart upload due to error', [
                    'file_name' => $originalFileName,
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
        $startTime = microtime(true);
        $maxConcurrency = config('multipart-upload.performance.concurrent_uploads', 3);
        $parts = [];
        $totalParts = count($chunks);
        $completedParts = 0;
        $totalUploadedSize = 0;

        Log::info('Starting parallel parts upload', [
            'file_name' => $fileName,
            'total_parts' => $totalParts,
            'max_concurrency' => $maxConcurrency,
            'chunk_size_mb' => round($this->chunkSize / 1024 / 1024, 2)
        ]);

        // Chia chunks thành batches để upload parallel
        $batches = array_chunk($chunks, $maxConcurrency);

        foreach ($batches as $batchIndex => $batch) {
            $batchStartTime = microtime(true);
            $promises = [];

            Log::info('Processing batch', [
                'file_name' => $fileName,
                'batch_index' => $batchIndex + 1,
                'total_batches' => count($batches),
                'batch_size' => count($batch)
            ]);

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
                    $totalUploadedSize += $result['size'] ?? 0;

                    // Log progress
                    $progress = round(($completedParts / $totalParts) * 100, 2);
                    $elapsedTime = round((microtime(true) - $startTime) * 1000, 2);
                    $avgSpeed = $elapsedTime > 0 ? round(($totalUploadedSize / 1024 / 1024) / (($elapsedTime / 1000)), 2) : 0;

                    Log::info("Parallel upload progress", [
                        'file_name' => $fileName,
                        'progress_percent' => $progress,
                        'completed_parts' => $completedParts,
                        'total_parts' => $totalParts,
                        'batch' => $batchIndex + 1,
                        'part' => $partNumber,
                        'elapsed_time_ms' => $elapsedTime,
                        'uploaded_size_mb' => round($totalUploadedSize / 1024 / 1024, 2),
                        'avg_speed_mbps' => $avgSpeed
                    ]);
                } else {
                    Log::error('Part upload failed', [
                        'file_name' => $fileName,
                        'part_number' => $partNumber,
                        'error' => $result['error']
                    ]);
                    throw new \Exception("Failed to upload part {$partNumber}: {$result['error']}");
                }
            }

            $batchTime = round((microtime(true) - $batchStartTime) * 1000, 2);
            Log::info('Batch completed', [
                'file_name' => $fileName,
                'batch_index' => $batchIndex + 1,
                'batch_time_ms' => $batchTime,
                'batch_size' => count($batch)
            ]);
        }

        // Sắp xếp parts theo part number
        usort($parts, function ($a, $b) {
            return $a['PartNumber'] - $b['PartNumber'];
        });

        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        $totalSizeMB = round($totalUploadedSize / 1024 / 1024, 2);
        $avgSpeed = $totalTime > 0 ? round($totalSizeMB / (($totalTime / 1000)), 2) : 0;

        Log::info("All parts uploaded successfully in parallel", [
            'file_name' => $fileName,
            'total_parts' => count($parts),
            'total_batches' => count($batches),
            'total_time_ms' => $totalTime,
            'total_size_mb' => $totalSizeMB,
            'avg_speed_mbps' => $avgSpeed
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
        $enableParallel = config('multipart-upload.performance.enable_parallel', true);

        if ($enableParallel && class_exists('GuzzleHttp\Promise\Promise')) {
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
     * True parallel upload using Guzzle Promises
     */
    protected function resolveUploadPromisesParallel(array $promises): array
    {
        $startTime = microtime(true);
        $maxConcurrency = config('multipart-upload.performance.concurrent_uploads', 5);

        Log::info('Starting parallel promise resolution', [
            'total_promises' => count($promises),
            'max_concurrency' => $maxConcurrency
        ]);

        // Chia promises thành batches để kiểm soát concurrency
        $batches = array_chunk($promises, $maxConcurrency, true);
        $results = [];

        foreach ($batches as $batchIndex => $batch) {
            $batchStartTime = microtime(true);

            Log::info("Processing batch {$batchIndex}", [
                'batch_index' => $batchIndex + 1,
                'total_batches' => count($batches),
                'batch_size' => count($batch)
            ]);

            // Thực hiện batch parallel
            $batchResults = $this->processBatchParallel($batch);
            $results = array_merge($results, $batchResults);

            $batchTime = round((microtime(true) - $batchStartTime) * 1000, 2);
            Log::info("Batch {$batchIndex} completed", [
                'batch_time_ms' => $batchTime,
                'successful_uploads' => count(array_filter($batchResults, fn($r) => $r['success']))
            ]);
        }

        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info('Parallel promise resolution completed', [
            'total_time_ms' => $totalTime,
            'total_results' => count($results)
        ]);

        return $results;
    }

    /**
     * Process a batch of uploads in parallel using Guzzle Promises
     */
    protected function processBatchParallel(array $batch): array
    {
        $results = [];
        $guzzlePromises = [];

        // Tạo Guzzle Promises cho batch
        foreach ($batch as $partNumber => $promise) {
            $guzzlePromises[$partNumber] = new \GuzzleHttp\Promise\Promise(function () use ($promise, $partNumber) {
                try {
                    $startTime = microtime(true);
                    $result = $promise();
                    $endTime = microtime(true);

                    $uploadTime = round(($endTime - $startTime) * 1000, 2);

                    Log::debug("Promise {$partNumber} executed", [
                        'part_number' => $partNumber,
                        'upload_time_ms' => $uploadTime
                    ]);

                    return [
                        'success' => true,
                        'result' => $result,
                        'upload_time' => $uploadTime
                    ];
                } catch (\Exception $e) {
                    Log::error("Promise {$partNumber} failed", [
                        'part_number' => $partNumber,
                        'error' => $e->getMessage()
                    ]);

                    throw $e;
                }
            });
        }

        // Thực hiện parallel execution
        try {
            $batchResults = \GuzzleHttp\Promise\Utils::unwrap($guzzlePromises);

            // Xử lý kết quả
            foreach ($batchResults as $partNumber => $batchResult) {
                if ($batchResult['success']) {
                    $results[$partNumber] = [
                        'success' => true,
                        'ETag' => $batchResult['result']['ETag'] ?? null,
                        'upload_time' => $batchResult['upload_time']
                    ];
                } else {
                    $results[$partNumber] = [
                        'success' => false,
                        'error' => 'Upload failed'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Batch parallel execution failed', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch)
            ]);

            // Fallback to sequential execution
            foreach ($batch as $partNumber => $promise) {
                try {
                    $startTime = microtime(true);
                    $result = $promise();
                    $endTime = microtime(true);

                    $results[$partNumber] = [
                        'success' => true,
                        'ETag' => $result['ETag'] ?? null,
                        'upload_time' => round(($endTime - $startTime) * 1000, 2)
                    ];
                } catch (\Exception $fallbackException) {
                    $results[$partNumber] = [
                        'success' => false,
                        'error' => $fallbackException->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Upload một part với retry logic
     */
    protected function uploadPartWithRetry(string $uploadId, string $key, int $partNumber, string $data, int $maxRetries = 3)
    {
        $startTime = microtime(true);
        $retries = 0;
        $dataSize = strlen($data);

        Log::debug('Starting part upload', [
            'part_number' => $partNumber,
            'data_size_mb' => round($dataSize / 1024 / 1024, 2)
        ]);

        while ($retries < $maxRetries) {
            try {
                $uploadStartTime = microtime(true);
                $result = $this->s3Client->uploadPart([
                    'Bucket' => $this->bucket,
                    'Key' => $key,
                    'UploadId' => $uploadId,
                    'PartNumber' => $partNumber,
                    'Body' => $data
                ]);
                $uploadTime = round((microtime(true) - $uploadStartTime) * 1000, 2);

                $totalTime = round((microtime(true) - $startTime) * 1000, 2);
                $speed = $uploadTime > 0 ? round(($dataSize / 1024 / 1024) / (($uploadTime / 1000)), 2) : 0;

                Log::debug('Part upload successful', [
                    'part_number' => $partNumber,
                    'upload_time_ms' => $uploadTime,
                    'total_time_ms' => $totalTime,
                    'speed_mbps' => $speed,
                    'retries' => $retries
                ]);

                return $result;
            } catch (S3Exception $e) {
                $retries++;
                $totalTime = round((microtime(true) - $startTime) * 1000, 2);

                Log::warning("Part upload failed, retry {$retries}/{$maxRetries}", [
                    'part_number' => $partNumber,
                    'data_size_mb' => round($dataSize / 1024 / 1024, 2),
                    'total_time_ms' => $totalTime,
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode()
                ]);

                if ($retries >= $maxRetries) {
                    Log::error('Part upload failed after max retries', [
                        'part_number' => $partNumber,
                        'max_retries' => $maxRetries,
                        'total_time_ms' => $totalTime,
                        'final_error' => $e->getMessage()
                    ]);
                    throw $e;
                }

                // Exponential backoff
                $backoffTime = pow(2, $retries);
                Log::debug('Waiting before retry', [
                    'part_number' => $partNumber,
                    'backoff_seconds' => $backoffTime
                ]);
                sleep($backoffTime);
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
     * Upload nhiều files đồng thời sử dụng parallel processing
     * 
     * @param array $files Array of UploadedFile
     * @param string $basePath Base path for uploads
     * @param array $options Upload options
     * @return array Array of upload results
     */
    public function uploadMultipleFilesParallel(array $files, string $basePath, array $options = []): array
    {
        $startTime = microtime(true);
        $filesCount = count($files);

        Log::info('Starting parallel upload for multiple files', [
            'files_count' => $filesCount,
            'base_path' => $basePath
        ]);

        $promises = [];
        $fileInfo = [];

        // Tạo promises cho tất cả files
        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            // Tạo unique filename
            $originalName = $file->getClientOriginalName();
            $normalizedName = str_replace(' ', '+', $originalName);
            $normalizedName = urlencode($normalizedName);
            $fileName = time() . '_' . ($index + 1) . '_' . $normalizedName;
            $destinationPath = $basePath . '/' . $fileName;

            // Lưu thông tin file
            $fileInfo[$index] = [
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'destination_path' => $destinationPath
            ];

            // Tạo promise cho file này
            $promises[$index] = function () use ($file, $destinationPath, $options, $index) {
                try {
                    $uploadPath = $this->uploadFile($file, $destinationPath, $options);

                    return [
                        'success' => $uploadPath !== false,
                        'error' => $uploadPath === false ? 'Upload failed' : null,
                        'path' => $uploadPath,
                        'index' => $index
                    ];
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'path' => null,
                        'index' => $index
                    ];
                }
            };
        }

        // Thực hiện parallel upload
        $uploadResults = $this->resolveUploadPromises($promises);

        // Tổ chức kết quả theo format mong muốn
        $results = [];
        foreach ($uploadResults as $index => $result) {
            if (isset($fileInfo[$index])) {
                $results[$index] = [
                    'success' => $result['success'],
                    'error' => $result['error'] ?? null,
                    'path' => $result['path'],
                    'original_name' => $fileInfo[$index]['original_name'],
                    'size' => $fileInfo[$index]['size']
                ];
            }
        }

        $endTime = microtime(true);
        $totalTime = round(($endTime - $startTime) * 1000, 2);
        $totalSize = array_sum(array_column($fileInfo, 'size'));
        $avgSpeed = $totalTime > 0 ? round(($totalSize / 1024 / 1024) / (($totalTime / 1000)), 2) : 0;

        Log::info('Parallel upload completed', [
            'files_count' => $filesCount,
            'successful_uploads' => count(array_filter($results, fn($r) => $r['success'])),
            'failed_uploads' => count(array_filter($results, fn($r) => !$r['success'])),
            'total_time_ms' => $totalTime,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'avg_speed_mbps' => $avgSpeed
        ]);

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
