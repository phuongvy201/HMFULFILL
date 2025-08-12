# Sửa lỗi Parallel Upload - Cập nhật mới nhất

## Vấn đề đã được giải quyết

### 1. Lỗi "Batch parallel execution failed"

**Mô tả**: Guzzle Promise bị từ chối với thông báo "The promise was rejected with reason: Invoking the wait callback did not resolve the promise"

**Nguyên nhân**:

-   Sử dụng `GuzzleHttp\Promise\Utils::unwrap()` với synchronous operations
-   Promise resolution không xử lý đúng cách khi có lỗi
-   Thiếu timeout cho promise wait

**Giải pháp đã áp dụng**:

-   Thay thế `unwrap()` bằng `settle()->wait()` để xử lý cả fulfilled và rejected promises
-   Thêm timeout configurable cho promise wait
-   Cải thiện error handling cho promise rejection
-   Thêm fallback sequential execution khi parallel thất bại

### 2. Lỗi "Undefined array key 'path'"

**Mô tả**: Kết quả upload thiếu key 'path' trong array trả về

**Nguyên nhân**:

-   Cấu trúc kết quả không nhất quán giữa parallel và sequential execution
-   Thiếu xử lý cho các trường hợp result khác nhau (string path, array với path, array với ETag)
-   Promise function trả về array với key 'path', nhưng processBatchParallel wrap trong 'result' key

**Giải pháp đã áp dụng**:

-   Đảm bảo cấu trúc kết quả nhất quán từ `processBatchParallel`
-   Thêm xử lý cho nhiều loại result khác nhau
-   Thêm debug logging để theo dõi cấu trúc result
-   Cải thiện validation trong `DesignController`
-   Sửa lại logic xử lý kết quả trong `uploadMultipleFilesParallel`

## Thay đổi code chi tiết

### 1. Cập nhật `processBatchParallel()` trong `S3MultipartUploadService.php`

```php
protected function processBatchParallel(array $batch): array
{
    $results = [];
    $batchSize = count($batch);
    $minBatchSizeForParallel = config('multipart-upload.performance.min_batch_size_for_parallel', 3);

    // Nếu batch size nhỏ, sử dụng sequential để tránh overhead của Guzzle Promises
    if ($batchSize < $minBatchSizeForParallel) {
        // Sequential execution cho batch nhỏ
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

    // Tạo promises...

    try {
        $promiseTimeout = config('multipart-upload.performance.promise_timeout', 300);

        // Sử dụng settle thay vì unwrap để xử lý cả fulfilled và rejected promises
        $settledPromises = \GuzzleHttp\Promise\Utils::settle($guzzlePromises);

        // Chờ tất cả promises hoàn thành với timeout
        $batchResults = $settledPromises->wait($promiseTimeout);

        // Xử lý kết quả...
    } catch (\Exception $e) {
        // Fallback to sequential execution khi parallel thất bại
        Log::info('Falling back to sequential execution', [
            'batch_size' => $batchSize
        ]);

        // Sequential fallback...
    }
}
```

### 2. Cập nhật `uploadMultipleFilesParallel()` để xử lý cấu trúc kết quả

```php
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
```

### 3. Cải thiện error handling trong `DesignController.php`

```php
// Log debug để kiểm tra cấu trúc uploadResults
Log::debug('Upload results received in DesignController', [
    'task_id' => $task->id,
    'total_results' => count($uploadResults),
    'results_keys' => array_keys($uploadResults),
    'sample_result' => !empty($uploadResults) ? array_slice($uploadResults, 0, 1, true) : null
]);

// Kiểm tra kết quả upload
foreach ($uploadResults as $index => $result) {
    // Log debug cho từng result
    Log::debug("Processing upload result {$index}", [
        'task_id' => $task->id,
        'index' => $index,
        'result_keys' => array_keys($result),
        'result' => $result
    ]);

    // Đảm bảo result có đầy đủ các key cần thiết
    if (!isset($result['success'])) {
        Log::error('Invalid upload result structure', [
            'task_id' => $task->id,
            'index' => $index,
            'result' => $result
        ]);
        throw new \Exception("Invalid upload result structure for file index {$index}");
    }

    if ($result['success']) {
        // Kiểm tra path có tồn tại không
        if (!isset($result['path']) || empty($result['path'])) {
            Log::error('Upload succeeded but path is missing', [
                'task_id' => $task->id,
                'index' => $index,
                'result' => $result,
                'result_keys' => array_keys($result)
            ]);
            $originalName = $result['original_name'] ?? 'Unknown';
            throw new \Exception("Upload succeeded but path is missing for file: {$originalName}");
        }

        $designPaths[] = $result['path'];

        Log::info('Design file uploaded successfully', [
            'task_id' => $task->id,
            'file_index' => $index + 1,
            'original_name' => $result['original_name'],
            'path' => $result['path'],
            'size' => $result['size']
        ]);
    } else {
        $errorMessage = $result['error'] ?? 'Unknown error';
        $originalName = $result['original_name'] ?? 'Unknown file';
        Log::error('File upload failed', [
            'task_id' => $task->id,
            'index' => $index,
            'error' => $errorMessage,
            'original_name' => $originalName,
            'result' => $result
        ]);
        throw new \Exception("File upload failed: {$errorMessage} (File: {$originalName})");
    }
}
```

### 4. Cập nhật configuration

```php
// config/multipart-upload.php
'performance' => [
    'enable_parallel' => env('S3_ENABLE_PARALLEL_UPLOAD', true),
    'concurrent_uploads' => env('S3_CONCURRENT_UPLOADS', 5),
    'memory_limit' => env('S3_UPLOAD_MEMORY_LIMIT', '1G'),
    'time_limit' => env('S3_UPLOAD_TIME_LIMIT', 1800),
    'batch_size' => env('S3_BATCH_SIZE', 5),
    'use_multipart_uploader' => env('S3_USE_MULTIPART_UPLOADER', true),
    'enable_gc' => env('S3_ENABLE_GC', true),
    'max_concurrent_files' => env('S3_MAX_CONCURRENT_FILES', 10),
    'min_batch_size_for_parallel' => env('S3_MIN_BATCH_SIZE_FOR_PARALLEL', 3), // Mới
    'promise_timeout' => env('S3_PROMISE_TIMEOUT', 300), // Mới
],
```

## Kết quả mong đợi

1. **Không còn lỗi "Batch parallel execution failed"**: Guzzle Promise sẽ được xử lý đúng cách với `settle()->wait()`
2. **Không còn lỗi "Undefined array key 'path'"**: Cấu trúc kết quả luôn nhất quán và có key 'path'
3. **Fallback tự động**: Khi parallel thất bại, hệ thống tự động chuyển sang sequential execution
4. **Debug logging tốt hơn**: Có thể theo dõi cấu trúc kết quả và quá trình xử lý
5. **Error handling chi tiết**: Thông tin debug đầy đủ để troubleshooting

## Hướng dẫn testing

### 1. Test với file nhỏ (batch size < min_batch_size_for_parallel)

```bash
# Upload 2 files nhỏ để test sequential execution
curl -X POST /api/designs/submit \
  -F "files[]=@small_file1.jpg" \
  -F "files[]=@small_file2.jpg"
```

### 2. Test với file lớn (batch size >= min_batch_size_for_parallel)

```bash
# Upload 5 files để test parallel execution
curl -X POST /api/designs/submit \
  -F "files[]=@large_file1.jpg" \
  -F "files[]=@large_file2.jpg" \
  -F "files[]=@large_file3.jpg" \
  -F "files[]=@large_file4.jpg" \
  -F "files[]=@large_file5.jpg"
```

### 3. Kiểm tra logs

```bash
# Theo dõi logs để xem quá trình xử lý
tail -f storage/logs/laravel.log | grep -E "(parallel|batch|promise|path|debug)"

# Tìm các log debug về cấu trúc kết quả
tail -f storage/logs/laravel.log | grep -E "(Upload results received|Processing upload result|Processing upload result for index)"
```

## Monitoring và Troubleshooting

### 1. Kiểm tra cấu trúc kết quả

```php
// Trong log, tìm các message debug:
Log::debug("Processing upload result for index {$index}", [...]);
Log::debug('Upload results received in DesignController', [...]);
```

### 2. Kiểm tra promise resolution

```php
// Tìm các message về promise execution:
Log::debug("Promise {$partNumber} executed", [...]);
Log::error("Promise {$partNumber} failed", [...]);
```

### 3. Kiểm tra fallback execution

```php
// Tìm message khi chuyển sang sequential:
Log::info('Falling back to sequential execution', [...]);
```

### 4. Kiểm tra error details

```php
// Tìm các error chi tiết:
Log::error('Upload succeeded but path is missing', [...]);
Log::error('File upload failed', [...]);
Log::error('Invalid upload result structure', [...]);
```

## Cấu hình môi trường

### Development

```env
S3_ENABLE_PARALLEL_UPLOAD=true
S3_CONCURRENT_UPLOADS=3
S3_MIN_BATCH_SIZE_FOR_PARALLEL=2
S3_PROMISE_TIMEOUT=60
LOG_LEVEL=debug
```

### Production

```env
S3_ENABLE_PARALLEL_UPLOAD=true
S3_CONCURRENT_UPLOADS=5
S3_MIN_BATCH_SIZE_FOR_PARALLEL=3
S3_PROMISE_TIMEOUT=300
LOG_LEVEL=info
```

## Lưu ý quan trọng

1. **Batch size nhỏ**: Với batch size < `min_batch_size_for_parallel`, hệ thống sẽ sử dụng sequential execution để tránh overhead của Guzzle Promises
2. **Promise timeout**: Đảm bảo `promise_timeout` đủ lớn để xử lý file lớn
3. **Fallback mechanism**: Hệ thống tự động chuyển sang sequential execution nếu parallel thất bại
4. **Debug logging**: Bật debug logging để theo dõi cấu trúc kết quả và quá trình xử lý
5. **Cấu trúc kết quả**: Đảm bảo luôn có key 'path' trong kết quả trả về

## Testing script

Đã tạo file `test_upload_structure.php` để test cấu trúc kết quả:

```bash
# Chạy test script
php test_upload_structure.php
```

## Kết luận

Với những thay đổi này, hệ thống upload parallel sẽ:

-   Xử lý đúng cách Guzzle Promise resolution
-   Đảm bảo cấu trúc kết quả nhất quán
-   Có fallback mechanism khi parallel thất bại
-   Cung cấp debug logging chi tiết để troubleshooting
-   Xử lý đúng các trường hợp result khác nhau

Hệ thống sẽ ổn định hơn và không còn gặp các lỗi "Batch parallel execution failed" và "Undefined array key 'path'".
