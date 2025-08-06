# Hướng dẫn sử dụng Chunk Upload

## Tổng quan

Hệ thống chunk upload được thiết kế để xử lý upload file lớn một cách hiệu quả, tránh timeout và lỗi server khi upload file nặng.

## Tính năng

### ✅ Đã hoàn thành:

-   **Chia file thành chunks**: Tự động chia file lớn thành các chunks 1MB
-   **Upload tuần tự**: Upload từng chunk một cách an toàn
-   **Progress tracking**: Hiển thị tiến trình upload real-time
-   **Error handling**: Xử lý lỗi và retry tự động
-   **File validation**: Kiểm tra định dạng và kích thước file
-   **Memory efficient**: Không load toàn bộ file vào memory
-   **Resume support**: Có thể resume upload nếu bị gián đoạn

### 🔧 Cấu hình:

-   **Chunk size**: 1MB per chunk
-   **Max file size**: 100MB per file
-   **Supported formats**: JPG, JPEG, PNG, PDF
-   **Storage**: S3 bucket với local temp storage

## Cách hoạt động

### 1. Frontend (JavaScript)

```javascript
// Khởi tạo upload manager
const uploadManager = new FileUploadManager({
    chunkSize: 1024 * 1024, // 1MB
    maxFileSize: 100 * 1024 * 1024, // 100MB
    onFileProgress: (data) => {
        // Cập nhật progress bar
        updateProgress(data.progress);
    },
    onFileComplete: (data) => {
        // File upload hoàn tất
        console.log("Uploaded:", data.filePath);
    },
});

// Upload file
await uploadManager.uploadFile(file);
```

### 2. Backend (Laravel)

```php
// Upload chunk
POST /customer/design/upload-chunk
{
    "file": "chunk_data",
    "chunk": 0,
    "chunks": 10,
    "filename": "design.jpg",
    "upload_id": "upload_123456",
    "total_size": 10485760
}

// Response
{
    "success": true,
    "completed": false,
    "uploaded_chunks": [0, 1, 2],
    "message": "Đã upload chunk 3/10"
}
```

## API Endpoints

### 1. Upload Chunk

```
POST /customer/design/upload-chunk
```

**Parameters:**

-   `file`: Chunk data
-   `chunk`: Chunk index (0-based)
-   `chunks`: Total number of chunks
-   `filename`: Original filename
-   `upload_id`: Unique upload ID
-   `total_size`: Total file size in bytes

### 2. Check Upload Status

```
GET /customer/design/upload-status/{uploadId}
```

**Response:**

```json
{
    "success": true,
    "metadata": {
        "filename": "design.jpg",
        "total_size": 10485760,
        "chunks": 10,
        "uploaded_chunks": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        "created_at": "2025-01-17T10:30:00.000000Z"
    },
    "progress": 100
}
```

### 3. Cancel Upload

```
POST /customer/design/upload-cancel
```

**Parameters:**

-   `upload_id`: Upload ID to cancel

## Quy trình upload

1. **Chọn file**: User chọn file từ form
2. **Validation**: Kiểm tra định dạng và kích thước
3. **Chia chunks**: Tự động chia file thành chunks 1MB
4. **Upload chunks**: Upload từng chunk tuần tự
5. **Progress tracking**: Hiển thị tiến trình real-time
6. **Merge chunks**: Ghép chunks thành file hoàn chỉnh
7. **Upload to S3**: Upload file hoàn chỉnh lên S3
8. **Cleanup**: Xóa chunks tạm và metadata

## Lợi ích

### 🚀 Performance

-   **Giảm timeout**: Không bị timeout khi upload file lớn
-   **Memory efficient**: Không load toàn bộ file vào memory
-   **Parallel processing**: Có thể upload nhiều file cùng lúc
-   **Resume capability**: Có thể resume nếu bị gián đoạn

### 🛡️ Reliability

-   **Error handling**: Xử lý lỗi network và server
-   **Retry mechanism**: Tự động retry khi chunk upload thất bại
-   **Validation**: Kiểm tra integrity của từng chunk
-   **Cleanup**: Tự động dọn dẹp file tạm

### 📊 User Experience

-   **Real-time progress**: Hiển thị tiến trình upload chi tiết
-   **File info**: Hiển thị thông tin file (tên, kích thước)
-   **Error messages**: Thông báo lỗi rõ ràng
-   **Cancel support**: Cho phép hủy upload

## Troubleshooting

### Lỗi thường gặp:

1. **"File quá lớn"**

    - Giải pháp: Giảm kích thước file hoặc tăng `maxFileSize`

2. **"Network timeout"**

    - Giải pháp: Tăng `chunkSize` hoặc giảm delay giữa chunks

3. **"Storage quota exceeded"**

    - Giải pháp: Kiểm tra dung lượng S3 và local storage

4. **"Chunk upload failed"**
    - Giải pháp: Kiểm tra network và server configuration

### Debug:

```javascript
// Enable debug logging
console.log("Upload debug:", {
    fileSize: file.size,
    chunks: Math.ceil(file.size / chunkSize),
    chunkSize: chunkSize,
});
```

## Cấu hình Server

### PHP Configuration:

```ini
; Tăng memory limit
memory_limit = 512M

; Tăng upload timeout
max_execution_time = 300
max_input_time = 300

; Tăng upload size
upload_max_filesize = 100M
post_max_size = 100M
```

### Laravel Configuration:

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],
```

## Monitoring

### Logs:

-   Chunk upload logs: `storage/logs/laravel.log`
-   Error tracking: Laravel Telescope (nếu có)

### Metrics:

-   Upload success rate
-   Average upload time
-   Chunk retry count
-   Storage usage

## Security

### Validation:

-   File type validation
-   File size limits
-   Upload frequency limits
-   User authentication

### Cleanup:

-   Automatic temp file cleanup
-   Expired upload cleanup
-   Failed upload cleanup

## Future Enhancements

### 🚀 Planned Features:

-   **Parallel chunk upload**: Upload nhiều chunks cùng lúc
-   **Resume upload**: Resume upload bị gián đoạn
-   **File compression**: Tự động nén file trước upload
-   **CDN integration**: Upload trực tiếp lên CDN
-   **Video support**: Hỗ trợ upload video files
-   **Batch upload**: Upload nhiều files cùng lúc

### 🔧 Technical Improvements:

-   **WebSocket progress**: Real-time progress via WebSocket
-   **Queue processing**: Background chunk processing
-   **Caching**: Redis cache cho upload metadata
-   **Monitoring**: Advanced monitoring và alerting
