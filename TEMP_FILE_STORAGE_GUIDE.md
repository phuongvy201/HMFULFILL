# 📁 Hướng dẫn Lưu trữ File Tạm thời - Trước khi Upload S3

## 🔍 Nơi lưu trữ tạm thời của ảnh

### 1. **System Temp Directory (Chính)**

**Vị trí**: `C:\Users\ZPC\AppData\Local\Temp` (Windows)

**Mô tả**: Đây là nơi chính mà PHP lưu trữ file tạm thời khi upload. Laravel sử dụng `UploadedFile::getRealPath()` để lấy đường dẫn thực tế của file tạm thời này.

**Code tham khảo**:

```php
// Trong S3MultipartUploadService.php
$filePath = $file->getRealPath(); // Trả về đường dẫn trong system temp
```

### 2. **Laravel Storage Temp**

**Vị trí**: `storage/app/temp/`

**Mô tả**: Laravel có thể lưu trữ file tạm thời trong thư mục storage của ứng dụng.

**Code tham khảo**:

```php
// Tạo file tạm thời trong Laravel storage
$tempFile = storage_path('app/temp/' . uniqid() . '.tmp');
```

### 3. **Upload Temp Directories**

**Các vị trí có thể**:

-   `storage/app/uploads/temp/`
-   `storage/app/public/uploads/temp/`
-   `public/uploads/temp/`

**Mô tả**: Các thư mục này được sử dụng cho việc lưu trữ tạm thời file upload.

## 🔧 Quy trình Upload File

### **Bước 1: File được upload từ browser**

```
Browser → PHP Upload Handler → System Temp Directory
```

### **Bước 2: Laravel xử lý file**

```php
// File được lưu tạm trong system temp
$uploadedFile = $request->file('design_file');
$tempPath = $uploadedFile->getRealPath(); // C:\Users\ZPC\AppData\Local\Temp\phpXXXXXX.tmp
```

### **Bước 3: Upload lên S3**

```php
// S3MultipartUploadService sử dụng file tạm để upload
$uploader = new MultipartUploader($this->s3Client, $filePath, [
    'Bucket' => $this->bucket,
    'Key' => $destinationPath,
    // ...
]);
```

### **Bước 4: Cleanup**

```php
// File tạm được tự động xóa sau khi upload xong
// hoặc được cleanup bởi system
```

## 📊 Kiểm tra File Tạm thời

### **1. Kiểm tra System Temp Directory**

```bash
# Windows
dir C:\Users\ZPC\AppData\Local\Temp

# Linux/Mac
ls -la /tmp/
```

### **2. Kiểm tra Laravel Storage**

```bash
# Kiểm tra storage/app/temp
ls -la storage/app/temp/

# Kiểm tra storage/app/public
ls -la storage/app/public/
```

### **3. Sử dụng Command Cleanup**

```bash
# Xem files tạm thời (dry run)
php artisan cleanup:temp-files --dry-run

# Xóa files tạm thời cũ
php artisan cleanup:temp-files --older-than=24
```

## 🔍 Debug File Tạm thời

### **1. Log File Path**

```php
// Thêm vào DesignController để debug
Log::info('Upload file temp path', [
    'original_name' => $file->getClientOriginalName(),
    'temp_path' => $file->getRealPath(),
    'temp_exists' => file_exists($file->getRealPath()),
    'temp_size' => filesize($file->getRealPath())
]);
```

### **2. Kiểm tra File Size**

```php
// Kiểm tra kích thước file tạm
$tempPath = $file->getRealPath();
$tempSize = filesize($tempPath);
$tempSizeMB = round($tempSize / 1024 / 1024, 2);

Log::info('Temp file info', [
    'path' => $tempPath,
    'size_mb' => $tempSizeMB,
    'readable' => is_readable($tempPath)
]);
```

### **3. Monitor Temp Directory**

```bash
# Windows - Monitor temp directory
powershell "Get-ChildItem C:\Users\ZPC\AppData\Local\Temp | Sort-Object LastWriteTime -Descending | Select-Object -First 10"

# Linux/Mac - Monitor temp directory
watch -n 5 "ls -la /tmp/ | head -20"
```

## 🚨 Vấn đề thường gặp

### **1. File tạm bị xóa quá sớm**

**Nguyên nhân**: System cleanup hoặc antivirus xóa file tạm

**Giải pháp**:

```php
// Copy file tạm sang thư mục an toàn
$safeTempPath = storage_path('app/temp/' . uniqid() . '_' . $file->getClientOriginalName());
copy($file->getRealPath(), $safeTempPath);

// Sử dụng file an toàn để upload
$uploader = new MultipartUploader($this->s3Client, $safeTempPath, [...]);
```

### **2. Disk space đầy**

**Kiểm tra**:

```bash
# Windows
dir C:\Users\ZPC\AppData\Local\Temp

# Linux/Mac
df -h /tmp/
```

**Giải pháp**:

```bash
# Cleanup temp files
php artisan cleanup:temp-files --older-than=1
```

### **3. Permission issues**

**Kiểm tra**:

```php
$tempPath = $file->getRealPath();
Log::info('Temp file permissions', [
    'path' => $tempPath,
    'readable' => is_readable($tempPath),
    'writable' => is_writable($tempPath),
    'permissions' => substr(sprintf('%o', fileperms($tempPath)), -4)
]);
```

## 📈 Monitoring và Maintenance

### **1. Auto Cleanup Command**

```bash
# Thêm vào crontab để tự động cleanup
0 2 * * * cd /path/to/project && php artisan cleanup:temp-files --older-than=24
```

### **2. Monitor Disk Space**

```bash
# Kiểm tra disk space của temp directories
php artisan diagnose:upload-issue
```

### **3. Log Monitoring**

```bash
# Monitor upload logs
tail -f storage/logs/laravel.log | grep "temp\|upload"
```

## 🎯 Best Practices

### **1. Sử dụng Safe Temp Path**

```php
// Thay vì sử dụng trực tiếp getRealPath()
$tempPath = $file->getRealPath();

// Nên copy sang thư mục an toàn
$safeTempPath = storage_path('app/temp/' . uniqid() . '_' . $file->getClientOriginalName());
copy($tempPath, $safeTempPath);

// Upload từ safe temp path
$uploader = new MultipartUploader($this->s3Client, $safeTempPath, [...]);

// Cleanup sau khi upload
unlink($safeTempPath);
```

### **2. Error Handling**

```php
try {
    $tempPath = $file->getRealPath();

    if (!file_exists($tempPath)) {
        throw new \Exception('Temp file not found: ' . $tempPath);
    }

    if (!is_readable($tempPath)) {
        throw new \Exception('Temp file not readable: ' . $tempPath);
    }

    // Proceed with upload
} catch (\Exception $e) {
    Log::error('Temp file issue', [
        'error' => $e->getMessage(),
        'temp_path' => $tempPath ?? 'unknown'
    ]);
    throw $e;
}
```

### **3. Regular Cleanup**

```bash
# Setup daily cleanup
php artisan schedule:work

# Hoặc manual cleanup
php artisan cleanup:temp-files --older-than=24
```

## 📝 Kết luận

-   **System Temp Directory** là nơi chính lưu trữ file tạm thời
-   File được lưu tạm trước khi upload lên S3
-   Cần monitor và cleanup thường xuyên
-   Sử dụng safe temp path để tránh lỗi
-   Setup auto cleanup để tránh disk space issues
