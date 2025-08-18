# 🚀 Hướng dẫn Nhanh - Sửa lỗi Upload File Lớn

## ⚡ Các bước thực hiện ngay

### 1. **Cập nhật file .env**

Thêm vào file `.env`:

```env
# Upload Performance Settings
S3_ENABLE_PARALLEL_UPLOAD=true
S3_CONCURRENT_UPLOADS=3
S3_UPLOAD_MEMORY_LIMIT=2G
S3_UPLOAD_TIME_LIMIT=3600
S3_BATCH_SIZE=3
S3_MAX_CONCURRENT_FILES=5
S3_UPLOAD_TIMEOUT=1800
S3_CHUNK_TIMEOUT=300
S3_ENABLE_PROGRESS_TRACKING=true
S3_PROGRESS_INTERVAL=5
```

### 2. **Cập nhật PHP Settings (nếu có thể)**

Trong `php.ini` hoặc `.htaccess`:

```ini
max_execution_time=3600
memory_limit=2G
upload_max_filesize=200M
post_max_size=200M
```

### 3. **Clear Cache**

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. **Test Upload**

Chạy script test để kiểm tra:

```bash
php test_upload_performance.php
```

## 🔧 Các cải thiện đã áp dụng

### ✅ **DesignController.php**

-   Tăng timeout lên 30 phút
-   Tăng memory limit lên 1GB
-   Thêm progress tracking
-   Cải thiện error handling
-   Thêm timeout control cho upload

### ✅ **config/multipart-upload.php**

-   Giảm concurrent uploads từ 5 xuống 3
-   Tăng memory limit lên 2GB
-   Tăng time limit lên 60 phút
-   Thêm progress tracking
-   Tối ưu batch size

## 📊 Monitoring

### Kiểm tra logs:

```bash
tail -f storage/logs/laravel.log | grep "upload"
```

### Kiểm tra progress:

```bash
tail -f storage/logs/laravel.log | grep "progress"
```

## 🚨 Troubleshooting

### Nếu vẫn bị timeout:

1. **Kiểm tra PHP settings:**

```bash
php -i | grep -E "(max_execution_time|memory_limit)"
```

2. **Giảm concurrent uploads:**

```env
S3_CONCURRENT_UPLOADS=2
S3_BATCH_SIZE=2
```

3. **Tăng timeout:**

```env
S3_UPLOAD_TIMEOUT=3600
```

### Nếu bị memory issues:

1. **Giảm memory usage:**

```env
S3_UPLOAD_MEMORY_LIMIT=1G
S3_MAX_CONCURRENT_FILES=3
```

2. **Kiểm tra server memory:**

```bash
free -h
```

## 📈 Kết quả mong đợi

-   ✅ Upload file lớn (lên đến 500MB)
-   ✅ Timeout 30-60 phút
-   ✅ Progress tracking
-   ✅ Error handling chi tiết
-   ✅ Memory usage tối ưu

## 🎯 Lưu ý quan trọng

1. **Server Resources**: Đảm bảo server có đủ RAM (tối thiểu 4GB)
2. **Network**: Kiểm tra bandwidth đến S3
3. **File Size**: Khuyến nghị không upload file > 500MB
4. **Concurrent Users**: Giới hạn số user upload đồng thời

## 📞 Hỗ trợ

Nếu vẫn gặp vấn đề:

1. Kiểm tra logs: `storage/logs/laravel.log`
2. Chạy test script: `php test_upload_performance.php`
3. Kiểm tra server resources
4. Liên hệ support với thông tin lỗi chi tiết

