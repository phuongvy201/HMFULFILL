# Chunk Upload Integration với DesignController

## Tổng quan

Hệ thống đã được tích hợp chunk upload cho việc upload files lên AWS S3 trong DesignController. Thay vì upload trực tiếp, files sẽ được chia thành các chunks nhỏ và upload từng phần.

## Các thay đổi đã thực hiện

### 1. DesignController.php

-   **Method `store()`**: Đã được cập nhật để nhận `uploaded_files[]` thay vì `mockup_files[]`
-   **Validation**: Loại bỏ validation cho file upload trực tiếp, thay vào đó kiểm tra số lượng uploaded files
-   **File handling**: Không còn xử lý upload trực tiếp, chỉ lưu file paths từ chunk upload

### 2. View (create.blade.php)

-   **JavaScript**: Đã tích hợp `ChunkUploadHandler` và `FileUploadManager`
-   **Form submission**: Files được upload trước khi submit form
-   **Progress tracking**: Hiển thị progress bar cho quá trình upload

### 3. Routes

-   `/customer/design/upload-chunk`: Upload chunks
-   `/customer/design/upload-status/{uploadId}`: Kiểm tra trạng thái upload
-   `/customer/design/upload-cancel`: Hủy upload

## Cách hoạt động

### 1. Frontend Process

```javascript
// 1. User chọn files
// 2. Files được thêm vào upload queue
uploadManager.addFiles(files);

// 3. Bắt đầu upload
await uploadManager.startUpload();

// 4. Sau khi upload xong, thêm file paths vào form
completedFiles.forEach((filePath, index) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "uploaded_files[]";
    input.value = filePath;
    form.appendChild(input);
});

// 5. Submit form
form.submit();
```

### 2. Backend Process

```php
// 1. Nhận uploaded file paths
$uploadedFiles = $request->uploaded_files ?? [];

// 2. Validation
if (count($uploadedFiles) !== $sidesCount) {
    return redirect()->back()->withErrors(['error' => 'Số lượng files không khớp']);
}

// 3. Lưu file paths
$mockupFilesJson = json_encode($uploadedFiles);

// 4. Tạo design task
$designTask = DesignTask::create([
    'mockup_file' => $mockupFilesJson,
    // ... other fields
]);
```

## Lợi ích

1. **Upload files lớn**: Có thể upload files lên đến 100MB
2. **Resume upload**: Có thể tiếp tục upload nếu bị gián đoạn
3. **Progress tracking**: Hiển thị tiến độ upload real-time
4. **Error handling**: Xử lý lỗi tốt hơn
5. **Memory efficient**: Không load toàn bộ file vào memory

## Cấu hình

### Chunk Size

-   Mặc định: 1MB per chunk
-   Có thể điều chỉnh trong JavaScript

### File Size Limit

-   Mặc định: 100MB per file
-   Có thể điều chỉnh trong JavaScript và Controller

### Storage

-   Files được lưu trên AWS S3
-   Temporary chunks được lưu trên local storage
-   Tự động cleanup sau khi upload xong

## Testing

Để test chunk upload:

1. Mở browser console
2. Load file test: `<script src="/js/test-chunk-upload.js"></script>`
3. Chạy: `testChunkUpload()`

## Troubleshooting

### Lỗi thường gặp

1. **CSRF Token**: Đảm bảo có CSRF token trong form
2. **File size**: Kiểm tra file size limit
3. **Storage permissions**: Đảm bảo S3 permissions đúng
4. **Memory limit**: Tăng PHP memory limit nếu cần

### Debug

-   Kiểm tra browser console cho JavaScript errors
-   Kiểm tra Laravel logs cho PHP errors
-   Sử dụng `Log::info()` để debug upload process

## Tương lai

-   Thêm resume upload functionality
-   Thêm parallel upload cho multiple files
-   Thêm file validation trước khi upload
-   Thêm upload queue management
