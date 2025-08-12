<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
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
            'timeout' => env('S3_UPLOAD_TIME_LIMIT', 300), // Tăng timeout lên 300s cho file lớn
            'connect_timeout' => 60, // Tăng connect timeout
            'http' => [
                'timeout' => env('S3_UPLOAD_TIME_LIMIT', 300),
                'connect_timeout' => 60,
            ],
            // Sử dụng S3 Transfer Acceleration nếu có thể
            'use_accelerate_endpoint' => env('S3_USE_ACCELERATE_ENDPOINT', false),
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
        $originalFileName = $file->getClientOriginalName();

        // Giảm logging để tránh IO bottleneck
        Log::info('Starting multipart upload with MultipartUploader', [
            'file_name' => $originalFileName,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'destination' => $destinationPath
        ]);

        try {
            // Sử dụng MultipartUploader của AWS SDK để stream upload
            $uploader = new MultipartUploader($this->s3Client, $filePath, [
                'Bucket' => $this->bucket,
                'Key' => $destinationPath,
                'ContentType' => $file->getMimeType(),
                'ACL' => $options['visibility'] ?? 'private',
                'Metadata' => array_merge([
                    'original-filename' => $originalFileName,
                    'upload-timestamp' => now()->toISOString()
                ], $options['metadata'] ?? []),
                'part_size' => $this->chunkSize,
                'concurrency' => config('multipart-upload.performance.concurrent_uploads', 5),
                'before_upload' => function () {
                    // Giải phóng memory cycles trước mỗi part upload
                    gc_collect_cycles();
                },
                'before_initiate' => function () {
                    // Tăng memory limit tạm thời nếu cần
                    $currentLimit = ini_get('memory_limit');
                    if ($currentLimit !== '-1') {
                        $currentBytes = $this->parseMemoryLimit($currentLimit);
                        if ($currentBytes < 1024 * 1024 * 1024) { // < 1GB
                            ini_set('memory_limit', '1G');
                        }
                    }
                }
            ]);

            // Thực hiện upload
            $result = $uploader->upload();

            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Multipart upload completed successfully', [
                'file_name' => $originalFileName,
                'destination' => $destinationPath,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'total_time_ms' => $totalTime,
                'speed_mbps' => round(($fileSize / 1024 / 1024) / (($totalTime / 1000)), 2),
                'upload_method' => 'MultipartUploader'
            ]);

            return $destinationPath;
        } catch (MultipartUploadException $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Multipart upload failed', [
                'file_name' => $originalFileName,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'total_time_ms' => $totalTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'upload_method' => 'MultipartUploader'
            ]);

            // MultipartUploader tự động abort nếu có lỗi
            throw $e;
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Unexpected error during multipart upload', [
                'file_name' => $originalFileName,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'total_time_ms' => $totalTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

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
        $batchSize = count($batch);
        $minBatchSizeForParallel = config('multipart-upload.performance.min_batch_size_for_parallel', 3);

        // Nếu batch size nhỏ, sử dụng sequential để tránh overhead của Guzzle Promises
        if ($batchSize < $minBatchSizeForParallel) {
            Log::debug('Using sequential execution for small batch', [
                'batch_size' => $batchSize
            ]);

            foreach ($batch as $partNumber => $promise) {
                try {
                    $startTime = microtime(true);
                    $result = $promise();
                    $endTime = microtime(true);

                    $results[$partNumber] = [
                        'success' => true,
                        'result' => $result,
                        'upload_time' => round(($endTime - $startTime) * 1000, 2)
                    ];
                } catch (\Exception $e) {
                    Log::error("Upload failed for part {$partNumber}", [
                        'part_number' => $partNumber,
                        'error' => $e->getMessage()
                    ]);

                    $results[$partNumber] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;
        }

        // Sử dụng Guzzle Promises cho batch lớn hơn
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

        // Thực hiện parallel execution với timeout
        try {
            $promiseTimeout = config('multipart-upload.performance.promise_timeout', 300);

            // Sử dụng settle thay vì unwrap để xử lý cả fulfilled và rejected promises
            $settledPromises = \GuzzleHttp\Promise\Utils::settle($guzzlePromises);

            // Chờ tất cả promises hoàn thành với timeout
            $batchResults = $settledPromises->wait($promiseTimeout);

            // Xử lý kết quả
            foreach ($batchResults as $partNumber => $batchResult) {
                if ($batchResult['state'] === 'fulfilled') {
                    $result = $batchResult['value'];
                    if ($result['success']) {
                        $results[$partNumber] = [
                            'success' => true,
                            'result' => $result['result'],
                            'upload_time' => $result['upload_time']
                        ];
                    } else {
                        $results[$partNumber] = [
                            'success' => false,
                            'error' => 'Upload failed'
                        ];
                    }
                } else {
                    // Promise bị rejected
                    $errorMessage = $batchResult['reason'] instanceof \Exception
                        ? $batchResult['reason']->getMessage()
                        : 'Unknown error';

                    $results[$partNumber] = [
                        'success' => false,
                        'error' => $errorMessage
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Batch parallel execution failed', [
                'error' => $e->getMessage(),
                'batch_size' => $batchSize
            ]);

            // Fallback to sequential execution khi parallel thất bại
            Log::info('Falling back to sequential execution', [
                'batch_size' => $batchSize
            ]);

            foreach ($batch as $partNumber => $promise) {
                try {
                    $startTime = microtime(true);
                    $result = $promise();
                    $endTime = microtime(true);

                    $results[$partNumber] = [
                        'success' => true,
                        'result' => $result,
                        'upload_time' => round(($endTime - $startTime) * 1000, 2)
                    ];
                } catch (\Exception $fallbackException) {
                    Log::error("Fallback upload failed for part {$partNumber}", [
                        'part_number' => $partNumber,
                        'error' => $fallbackException->getMessage()
                    ]);

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

        // Giảm logging để tránh IO bottleneck
        Log::info('Starting parallel upload for multiple files', [
            'files_count' => $filesCount,
            'base_path' => $basePath,
            'concurrent_uploads' => config('multipart-upload.performance.concurrent_uploads', 5)
        ]);

        $promises = [];
        $fileInfo = [];
        $baseTimestamp = microtime(true) * 1000; // Sử dụng microtime để tránh duplicate filename

        // Tạo promises cho tất cả files
        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            // Tạo unique filename với microtime để tránh duplicate
            $originalName = $file->getClientOriginalName();
            $normalizedName = str_replace(' ', '+', $originalName);
            $normalizedName = urlencode($normalizedName);
            $fileName = sprintf('%.0f_%d_%s', $baseTimestamp + $index, $index + 1, $normalizedName);
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

        // Thực hiện parallel upload với giới hạn concurrency
        $uploadResults = $this->resolveUploadPromisesParallel($promises);

        // Log debug information về kết quả
        Log::debug('Upload results structure', [
            'total_results' => count($uploadResults),
            'results_keys' => array_keys($uploadResults),
            'sample_result' => !empty($uploadResults) ? array_slice($uploadResults, 0, 1, true) : null
        ]);

        // Tổ chức kết quả theo format mong muốn
        $results = [];
        foreach ($uploadResults as $index => $result) {
            if (isset($fileInfo[$index])) {
                // Đảm bảo luôn có key 'path' để tránh "Undefined array key 'path'"
                $path = null;
                $success = false;
                $error = null;

                if ($result['success']) {
                    // Kiểm tra cấu trúc result từ processBatchParallel
                    if (isset($result['result'])) {
                        // Nếu result là array có key 'path' (từ promise function)
                        if (is_array($result['result']) && isset($result['result']['path'])) {
                            $path = $result['result']['path'];
                            $success = $result['result']['success'] ?? true;
                            $error = $result['result']['error'] ?? null;
                        }
                        // Nếu result là string (path trực tiếp)
                        elseif (is_string($result['result'])) {
                            $path = $result['result'];
                            $success = true;
                        }
                        // Nếu result là array có key 'ETag' (từ multipart upload)
                        elseif (is_array($result['result']) && isset($result['result']['ETag'])) {
                            $path = $fileInfo[$index]['destination_path'];
                            $success = true;
                        }
                    }
                } else {
                    $error = $result['error'] ?? 'Unknown error';
                }

                // Log debug để kiểm tra cấu trúc result
                Log::debug("Processing upload result for index {$index}", [
                    'success' => $success,
                    'result_type' => gettype($result['result'] ?? null),
                    'result_keys' => is_array($result['result'] ?? null) ? array_keys($result['result'] ?? []) : null,
                    'path' => $path,
                    'error' => $error
                ]);

                $results[$index] = [
                    'success' => $success,
                    'error' => $error,
                    'path' => $path, // Luôn có key này
                    'original_name' => $fileInfo[$index]['original_name'],
                    'size' => $fileInfo[$index]['size']
                ];
            }
        }

        $endTime = microtime(true);
        $totalTime = round(($endTime - $startTime) * 1000, 2);
        $totalSize = array_sum(array_column($fileInfo, 'size'));
        $avgSpeed = $totalTime > 0 ? round(($totalSize / 1024 / 1024) / (($totalTime / 1000)), 2) : 0;
        $successfulCount = count(array_filter($results, fn($r) => $r['success']));
        $failedCount = count(array_filter($results, fn($r) => !$r['success']));

        // Log kết quả tổng hợp thay vì chi tiết từng file
        Log::info('Parallel upload completed', [
            'files_count' => $filesCount,
            'successful_uploads' => $successfulCount,
            'failed_uploads' => $failedCount,
            'total_time_ms' => $totalTime,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'avg_speed_mbps' => $avgSpeed,
            'success_rate_percent' => $filesCount > 0 ? round(($successfulCount / $filesCount) * 100, 2) : 0
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
     * Parse memory limit string to bytes
     */
    protected function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
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
