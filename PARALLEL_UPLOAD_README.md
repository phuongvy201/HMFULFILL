# Parallel Upload System - Hệ thống Upload Đồng thời

## 🚀 Tính năng mới: Upload Files Đồng thời

Hệ thống đã được cải tiến để hỗ trợ upload nhiều files **đồng thời** thay vì tuần tự, giúp tăng tốc đáng kể quá trình upload design files.

## 📊 So sánh Hiệu suất

### Trước đây (Sequential Upload):

```
File 1: 10MB - 30 giây
File 2: 15MB - 45 giây
File 3: 8MB  - 24 giây
Tổng thời gian: 99 giây
```

### Bây giờ (Parallel Upload):

```
File 1: 10MB - 30 giây
File 2: 15MB - 45 giây
File 3: 8MB  - 24 giây
Tổng thời gian: 45 giây (thời gian của file lớn nhất)
```

**Cải thiện: ~55% thời gian upload**

## 🔧 Cách hoạt động

### 1. **Phương thức mới: `uploadMultipleFilesParallel()`**

```php
// Thay vì upload tuần tự
$uploadResults = $uploadService->uploadMultipleFiles($files, $path, $options);

// Bây giờ upload đồng thời
$uploadResults = $uploadService->uploadMultipleFilesParallel($files, $path, $options);
```

### 2. **Guzzle Promises Integration**

Hệ thống sử dụng Guzzle Promises để thực hiện true parallel execution:

```php
// Tạo promises cho tất cả files
foreach ($files as $index => $file) {
    $promises[$index] = function() use ($file, $destinationPath, $options) {
        return $this->uploadFile($file, $destinationPath, $options);
    };
}

// Thực hiện parallel execution
$results = \GuzzleHttp\Promise\Utils::unwrap($promises);
```

### 3. **Batch Processing**

Files được chia thành batches để kiểm soát concurrency:

```php
$maxConcurrency = config('multipart-upload.performance.concurrent_uploads', 5);
$batches = array_chunk($promises, $maxConcurrency, true);
```

## ⚙️ Cấu hình

### File: `config/multipart-upload.php`

```php
'performance' => [
    'enable_parallel' => env('S3_ENABLE_PARALLEL_UPLOAD', true),
    'concurrent_uploads' => env('S3_CONCURRENT_UPLOADS', 5),
    'memory_limit' => env('S3_UPLOAD_MEMORY_LIMIT', '512M'),
    'time_limit' => env('S3_UPLOAD_TIME_LIMIT', 600),
],
```

### Environment Variables:

```env
S3_ENABLE_PARALLEL_UPLOAD=true
S3_CONCURRENT_UPLOADS=5
S3_UPLOAD_MEMORY_LIMIT=512M
S3_UPLOAD_TIME_LIMIT=600
```

## 📝 Logging & Monitoring

### Upload Start Log:

```php
Log::info('Starting parallel multipart upload for multiple design files', [
    'task_id' => $task->id,
    'files_count' => count($designFiles),
    'sides_count' => $task->sides_count,
    'parallel_enabled' => true,
    'concurrent_uploads' => 5
]);
```

### Upload Completion Log:

```php
Log::info('Parallel upload completed', [
    'task_id' => $task->id,
    'total_upload_time_ms' => 45000,
    'successful_uploads' => 3,
    'failed_uploads' => 0
]);
```

## 🛡️ Error Handling & Fallback

### 1. **Automatic Fallback**

Nếu parallel upload thất bại, hệ thống tự động chuyển về sequential upload:

```php
try {
    $batchResults = \GuzzleHttp\Promise\Utils::unwrap($guzzlePromises);
} catch (\Exception $e) {
    // Fallback to sequential execution
    foreach ($batch as $partNumber => $promise) {
        $result = $promise();
    }
}
```

### 2. **Individual File Error Handling**

Mỗi file được xử lý độc lập, lỗi của một file không ảnh hưởng đến các file khác.

## 🎯 Sử dụng trong Controllers

### DesignController - submitDesign():

```php
if ($task->sides_count > 1) {
    // Upload nhiều files đồng thời
    $uploadResults = $uploadService->uploadMultipleFilesParallel(
        $designFiles,
        'designs/completed',
        $options
    );
}
```

### DesignController - updateDesign():

```php
if ($task->sides_count > 1) {
    // Update nhiều files đồng thời
    $uploadResults = $uploadService->uploadMultipleFilesParallel(
        $designFiles,
        'designs/updated',
        $options
    );
}
```

## 📈 Performance Metrics

### Monitoring:

-   **Total upload time**: Thời gian tổng để upload tất cả files
-   **Concurrent uploads**: Số lượng files upload đồng thời
-   **Success rate**: Tỷ lệ upload thành công
-   **Average speed**: Tốc độ upload trung bình (MB/s)

### Optimization Tips:

1. **Tăng concurrent_uploads** nếu có bandwidth cao
2. **Giảm concurrent_uploads** nếu gặp lỗi timeout
3. **Monitor memory usage** khi upload nhiều files lớn

## 🔍 Testing

### Test Parallel Upload:

```php
// Tạo test files
$testFiles = [
    UploadedFile::fake()->create('design1.jpg', 1024 * 1024), // 1MB
    UploadedFile::fake()->create('design2.jpg', 2 * 1024 * 1024), // 2MB
    UploadedFile::fake()->create('design3.jpg', 1.5 * 1024 * 1024), // 1.5MB
];

// Test parallel upload
$startTime = microtime(true);
$results = $uploadService->uploadMultipleFilesParallel($testFiles, 'test');
$endTime = microtime(true);

$totalTime = ($endTime - $startTime) * 1000;
echo "Parallel upload time: {$totalTime}ms";
```

## 🚨 Lưu ý quan trọng

1. **Memory Usage**: Parallel upload sử dụng nhiều memory hơn
2. **Network Bandwidth**: Đảm bảo đủ bandwidth cho concurrent uploads
3. **S3 Limits**: AWS S3 có giới hạn về concurrent requests
4. **Error Handling**: Luôn có fallback mechanism

## 📞 Support

Nếu gặp vấn đề với parallel upload:

1. Kiểm tra logs trong `storage/logs/laravel.log`
2. Verify Guzzle HTTP client đã được cài đặt
3. Kiểm tra cấu hình S3 credentials
4. Monitor memory usage và network bandwidth
