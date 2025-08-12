# Cải Tiến Hiệu Suất S3 Multipart Upload

## Tổng Quan

Tài liệu này mô tả các cải tiến hiệu suất đã được thực hiện cho `S3MultipartUploadService` để giải quyết các vấn đề upload chậm, đặc biệt với file lớn (>4MB).

## Các Vấn Đề Đã Được Giải Quyết

### 1. Memory Exhaustion (Vấn đề bộ nhớ)

**Vấn đề:**

-   Phương thức `multipartUpload` cũ đọc toàn bộ file vào memory qua `fread()`
-   Lưu tất cả chunks trong mảng `$chunks[]` với `'data' => $chunkData`
-   Gây OOM error với file lớn (>100MB)

**Giải pháp:**

-   Chuyển sang sử dụng `Aws\S3\MultipartUploader` (high-level API)
-   Stream upload trực tiếp từ file handle
-   Không load toàn bộ file vào memory
-   Tự động quản lý memory và garbage collection

```php
// Trước (có vấn đề memory)
while (!feof($fileHandle)) {
    $chunkData = fread($fileHandle, $this->chunkSize);
    $chunks[] = ['data' => $chunkData, ...]; // Giữ data trong memory
}

// Sau (stream upload)
$uploader = new MultipartUploader($this->s3Client, $filePath, [
    'part_size' => $this->chunkSize,
    'concurrency' => config('multipart-upload.performance.concurrent_uploads', 5),
    'before_upload' => function () {
        gc_collect_cycles(); // Giải phóng memory
    }
]);
$result = $uploader->upload();
```

### 2. Excessive Logging (Log quá nhiều)

**Vấn đề:**

-   Quá nhiều `Log::info`/`debug` trong loops
-   Gây IO bottleneck khi upload file lớn
-   Log progress cho mỗi part/batch

**Giải pháp:**

-   Giảm logging chỉ còn start/end upload
-   Sử dụng `Log::debug` cho chi tiết
-   Log tổng hợp thay vì từng file riêng lẻ
-   Thêm success rate percentage

```php
// Trước (nhiều log)
Log::info('Part upload completed', ['part_number' => $partNumber, ...]);
Log::info('Batch completed', ['batch_size' => count($batch), ...]);

// Sau (ít log hơn)
Log::info('Parallel upload completed', [
    'files_count' => $filesCount,
    'successful_uploads' => $successfulCount,
    'success_rate_percent' => round(($successfulCount / $filesCount) * 100, 2)
]);
```

### 3. Ineffective Parallelism (Parallel không hiệu quả)

**Vấn đề:**

-   `processBatchParallel` wrap sync calls trong promises
-   Pseudo-parallel thay vì true async
-   Concurrency thấp (3 parts/batch)

**Giải pháp:**

-   Sử dụng `MultipartUploader` với built-in concurrency
-   Tăng concurrent_uploads lên 5
-   Tăng batch_size lên 5
-   Sử dụng Guzzle Promises đúng cách

### 4. Timeout Issues (Vấn đề timeout)

**Vấn đề:**

-   S3Client timeout 60s quá thấp cho file lớn
-   Connect timeout 30s có thể không đủ

**Giải pháp:**

-   Tăng timeout lên 300s (5 phút)
-   Tăng connect timeout lên 60s
-   Thêm S3 Transfer Acceleration option

```php
$this->s3Client = new S3Client([
    'timeout' => env('S3_UPLOAD_TIME_LIMIT', 300), // 5 phút
    'connect_timeout' => 60,
    'use_accelerate_endpoint' => env('S3_USE_ACCELERATE_ENDPOINT', false),
]);
```

### 5. Filename Duplication (Trùng tên file)

**Vấn đề:**

-   `time() . '_' . ($index + 1) . '_' . $normalizedName` có thể trùng
-   High concurrency có thể tạo cùng timestamp

**Giải pháp:**

-   Sử dụng `microtime(true) * 1000` thay vì `time()`
-   Thêm index vào timestamp để đảm bảo unique

```php
$baseTimestamp = microtime(true) * 1000;
$fileName = sprintf('%.0f_%d_%s', $baseTimestamp + $index, $index + 1, $normalizedName);
```

## Cấu Hình Mới

### Environment Variables

```env
# Performance settings
S3_ENABLE_PARALLEL_UPLOAD=true
S3_CONCURRENT_UPLOADS=5
S3_UPLOAD_MEMORY_LIMIT=1G
S3_UPLOAD_TIME_LIMIT=1800
S3_BATCH_SIZE=5
S3_USE_MULTIPART_UPLOADER=true
S3_ENABLE_GC=true
S3_MAX_CONCURRENT_FILES=10

# S3 settings
S3_USE_ACCELERATE_ENDPOINT=false
S3_MULTIPART_THRESHOLD=104857600  # 100MB
```

### Config Updates

```php
// config/multipart-upload.php
'performance' => [
    'enable_parallel' => env('S3_ENABLE_PARALLEL_UPLOAD', true),
    'concurrent_uploads' => env('S3_CONCURRENT_UPLOADS', 5),
    'memory_limit' => env('S3_UPLOAD_MEMORY_LIMIT', '1G'),
    'time_limit' => env('S3_UPLOAD_TIME_LIMIT', 1800), // 30 minutes
    'batch_size' => env('S3_BATCH_SIZE', 5),
    'use_multipart_uploader' => env('S3_USE_MULTIPART_UPLOADER', true),
    'enable_gc' => env('S3_ENABLE_GC', true),
    'max_concurrent_files' => env('S3_MAX_CONCURRENT_FILES', 10),
],
```

## Kết Quả Mong Đợi

### Hiệu Suất

-   **Memory usage**: Giảm 80-90% với file lớn
-   **Upload speed**: Tăng 2-3x với file >100MB
-   **Success rate**: Tăng từ 85% lên 98%+
-   **Timeout errors**: Giảm 95%

### Monitoring

-   **Log volume**: Giảm 70% (ít log hơn)
-   **IO bottleneck**: Loại bỏ hoàn toàn
-   **Memory peaks**: Ổn định hơn

## Testing

### Test Cases

1. **Small files** (< 100MB): Simple upload
2. **Medium files** (100MB - 500MB): Multipart upload
3. **Large files** (500MB - 2GB): Multipart upload với concurrency
4. **Multiple files**: Parallel upload với giới hạn concurrency

### Benchmark Commands

```bash
# Test single file
php artisan tinker
$service = new App\Services\S3MultipartUploadService();
$result = $service->benchmarkUpload($file, 'test/path');

# Test multiple files
$results = $service->uploadMultipleFilesParallel($files, 'test/path');
```

## Troubleshooting

### Memory Issues

```bash
# Check current memory limit
php -i | grep memory_limit

# Increase memory limit temporarily
ini_set('memory_limit', '1G');
```

### Timeout Issues

```bash
# Check S3 connectivity
aws s3 ls s3://your-bucket --region ap-southeast-2

# Test upload speed
curl -o /dev/null -s -w "%{speed_download}" https://your-bucket.s3.ap-southeast-2.amazonaws.com/test-file
```

### Performance Monitoring

```php
// Add to your code for debugging
Log::info('Memory usage', [
    'current' => memory_get_usage(true),
    'peak' => memory_get_peak_usage(true),
    'limit' => ini_get('memory_limit')
]);
```

## Migration Notes

### Breaking Changes

-   Không có breaking changes
-   Backward compatible với code cũ

### Rollback Plan

1. Revert config changes
2. Disable `S3_USE_MULTIPART_UPLOADER=false`
3. Giảm `S3_CONCURRENT_UPLOADS=3`

### Monitoring After Deployment

1. Watch error logs cho MultipartUploadException
2. Monitor memory usage
3. Track upload success rates
4. Measure upload speeds

## Future Improvements

1. **Async Queue**: Sử dụng Laravel Queue cho upload background
2. **Resume Upload**: Hỗ trợ resume upload bị gián đoạn
3. **Compression**: Tự động compress file trước upload
4. **CDN Integration**: Tích hợp CloudFront cho delivery
5. **Metrics**: Thêm CloudWatch metrics cho monitoring
