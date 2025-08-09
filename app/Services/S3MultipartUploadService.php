<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

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
            // Bước 2: Upload từng part
            $fileHandle = fopen($filePath, 'rb');
            $partNumber = 1;
            $uploadedBytes = 0;

            while (!feof($fileHandle)) {
                $chunkData = fread($fileHandle, $this->chunkSize);
                $chunkSize = strlen($chunkData);

                if ($chunkSize === 0) {
                    break;
                }

                // Upload part với retry logic
                $partResult = $this->uploadPartWithRetry(
                    $uploadId,
                    $destinationPath,
                    $partNumber,
                    $chunkData,
                    3 // max retries
                );

                if (!$partResult) {
                    throw new \Exception("Failed to upload part {$partNumber}");
                }

                $parts[] = [
                    'ETag' => $partResult['ETag'],
                    'PartNumber' => $partNumber
                ];

                $uploadedBytes += $chunkSize;
                $partNumber++;

                // Log progress
                $progress = round(($uploadedBytes / $fileSize) * 100, 2);
                Log::info("Upload progress: {$progress}%", [
                    'file' => $fileName,
                    'part' => $partNumber - 1,
                    'uploaded' => $uploadedBytes,
                    'total' => $fileSize
                ]);
            }

            fclose($fileHandle);

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
}
