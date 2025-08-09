# S3 Multipart Upload System

Hệ thống upload design files sử dụng AWS S3 Multipart Upload để xử lý files lớn một cách hiệu quả và đáng tin cậy.

## 🚀 Tính năng chính

### ✅ Smart Upload Strategy

-   **Files < 100MB**: Simple upload (nhanh, hiệu quả)
-   **Files ≥ 100MB**: Multipart upload (đáng tin cậy, có thể resume)
-   **Auto chunk size**: Tự động điều chỉnh theo kích thước file

### ✅ Reliability & Recovery

-   **Retry logic**: Exponential backoff khi upload failed
-   **Auto cleanup**: Tự động xóa incomplete uploads (10% chance mỗi lần upload)
-   **Error handling**: Comprehensive error logging và recovery

### ✅ Performance Optimization

-   **Parallel uploads**: Upload nhiều files đồng thời
-   **Optimal chunk sizes**: 5MB → 50MB tùy theo file size
-   **Memory management**: Tự động điều chỉnh memory limit

### ✅ Security & Validation

-   **File integrity**: Magic byte validation
-   **MIME type**: Kiểm tra MIME type thực tế
-   **Size limits**: Configurable file size limits
-   **Extension validation**: Whitelist allowed extensions

## 📁 Cấu trúc Files

```
app/
├── Services/
│   └── S3MultipartUploadService.php          # Core service
├── Http/
│   ├── Controllers/Admin/
│   │   └── S3ManagementController.php         # Admin management
│   ├── Requests/
│   │   └── DesignUploadRequest.php            # Form validation
│   └── Middleware/
│       └── UploadProgressMiddleware.php       # Upload monitoring
├── Console/Commands/
│   └── CleanupIncompleteUploads.php           # Manual cleanup command
config/
└── multipart-upload.php                      # Configuration
resources/views/admin/
└── s3-management/
    └── index.blade.php                        # Admin UI
```

## ⚙️ Configuration

### File: `config/multipart-upload.php`

```php
// Threshold để sử dụng multipart upload
'multipart_threshold' => 100 * 1024 * 1024, // 100MB

// Chunk sizes cho các loại file
'chunk_sizes' => [
    'small' => 5 * 1024 * 1024,   // 5MB
    'medium' => 10 * 1024 * 1024, // 10MB
    'large' => 20 * 1024 * 1024,  // 20MB
    'xlarge' => 50 * 1024 * 1024, // 50MB
],

// Auto cleanup settings
'cleanup' => [
    'auto_cleanup_enabled' => true,
    'incomplete_upload_ttl_hours' => 24,
],

// File type restrictions
'file_types' => [
    'design_files' => [
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'psd'],
        'max_file_size' => 200 * 1024 * 1024, // 200MB
    ],
],
```

## 🛠️ Sử dụng

### 1. Upload Design Files (Automatic)

Hệ thống tự động sử dụng multipart upload khi designer submit design:

```php
// Trong DesignController
$uploadService = new S3MultipartUploadService();
$uploadPath = $uploadService->uploadFile($file, $destinationPath, $options);
```

### 2. Admin Management UI

Truy cập: `/admin/s3-management`

**Tính năng:**

-   📊 Xem thống kê incomplete uploads
-   🧹 Manual cleanup
-   ❌ Abort specific uploads
-   🔄 Refresh real-time stats

### 3. Manual Cleanup Command

```bash
# Cleanup uploads older than 24 hours
php artisan uploads:cleanup

# Cleanup uploads older than 48 hours
php artisan uploads:cleanup --hours=48

# Dry run - xem sẽ cleanup gì mà không thực sự xóa
php artisan uploads:cleanup --dry-run
```

## 📊 Monitoring & Logging

### Upload Logs

```php
// Upload start
Log::info('Starting multipart upload for multiple design files', [
    'task_id' => $task->id,
    'files_count' => count($designFiles),
    'sides_count' => $task->sides_count
]);

// Upload progress
Log::info("Upload progress: {$progress}%", [
    'file' => $fileName,
    'part' => $partNumber,
    'uploaded' => $uploadedBytes,
    'total' => $fileSize
]);

// Upload complete
Log::info('Multipart upload completed successfully', [
    'file' => $fileName,
    'destination' => $destinationPath,
    'parts' => count($parts),
    'size' => $fileSize
]);
```

### Performance Monitoring

```php
// Trong UploadProgressMiddleware
Log::info('Design upload request completed', [
    'user_id' => Auth::id(),
    'duration_ms' => $duration,
    'status_code' => $response->getStatusCode(),
    'memory_peak' => memory_get_peak_usage(true)
]);
```

## 🔧 Troubleshooting

### Common Issues

**1. Upload Timeout**

```php
// Solution: Tăng timeout trong config
'performance' => [
    'time_limit' => 900, // 15 minutes
    'memory_limit' => '1024M',
],
```

**2. Memory Limit**

```php
// Auto-adjusted in UploadProgressMiddleware
if ($totalSize > 50 * 1024 * 1024) {
    set_time_limit(600);
    ini_set('memory_limit', '512M');
}
```

**3. Too Many Incomplete Uploads**

```bash
# Manual cleanup
php artisan uploads:cleanup --hours=1

# Or via Admin UI
/admin/s3-management → Cleanup button
```

**4. File Validation Errors**

```php
// Kiểm tra trong DesignUploadRequest
protected function validateFileIntegrity($file, $fail)
{
    // Magic byte validation
    // MIME type verification
    // File size checks
}
```

## 🔒 Security Considerations

### File Validation

-   **Magic bytes**: Kiểm tra file signature
-   **MIME type**: Verify actual content type
-   **Extension**: Whitelist allowed extensions
-   **Size limits**: Prevent oversized uploads

### Access Control

-   **Authentication**: Required for all uploads
-   **Role-based**: Only designers can upload
-   **Admin-only**: S3 management interface

### S3 Security

-   **Private uploads**: All files private by default
-   **Metadata**: Tracking upload source
-   **Encryption**: Server-side encryption enabled

## 📈 Performance Metrics

### Chunk Size Strategy

```
File Size        | Chunk Size | Upload Strategy
≤ 50MB          | 5MB        | Simple/Multipart
≤ 500MB         | 10MB       | Multipart
≤ 2GB           | 20MB       | Multipart
> 2GB           | 50MB       | Multipart
```

### Expected Performance

-   **Small files (< 100MB)**: 1-5 seconds
-   **Medium files (100MB-1GB)**: 30 seconds - 5 minutes
-   **Large files (> 1GB)**: 5-20 minutes

### Auto Cleanup Schedule

-   **Trigger**: 10% chance mỗi lần upload
-   **Default TTL**: 24 hours
-   **Manual**: Via Admin UI hoặc Artisan command

## 🚨 Alerts & Monitoring

### Error Scenarios

1. **Upload failures**: Automatic retry với exponential backoff
2. **Incomplete uploads**: Auto cleanup sau 24h
3. **Memory issues**: Auto memory limit adjustment
4. **Timeout**: Configurable time limits

### Success Metrics

-   Upload completion rate
-   Average upload time
-   Retry success rate
-   Cleanup efficiency

## 💡 Best Practices

### For Developers

1. **Always validate files** trước khi upload
2. **Use form requests** cho validation logic
3. **Monitor logs** cho performance issues
4. **Regular cleanup** để tránh storage waste

### For Admins

1. **Monitor S3 costs** thường xuyên
2. **Check incomplete uploads** weekly
3. **Review error logs** cho patterns
4. **Adjust thresholds** theo usage patterns

### For Users

1. **Upload files < 200MB** per file
2. **Stable internet** cho large uploads
3. **Avoid refreshing** during upload
4. **Check file formats** trước upload

---

## 🔄 Migration từ Simple Upload

Hệ thống tự động fallback về simple upload cho files nhỏ, không cần migration manual.

### Backward Compatibility

-   ✅ Existing uploads vẫn hoạt động
-   ✅ Same API interface
-   ✅ Same file paths
-   ✅ Same validation rules

Hệ thống đã sẵn sàng sử dụng! 🎉
