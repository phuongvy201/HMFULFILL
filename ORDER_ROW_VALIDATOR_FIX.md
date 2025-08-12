# Sửa lỗi "Path cannot be empty" trong OrderRowValidator

## Vấn đề

**Lỗi:** `Path cannot be empty {"userId":16,"exception":"[object] (ValueError(code: 0): Path cannot be empty at C:\\laragon\\www\\fulfill-HM\\app\\Services\\OrderRowValidator.php:345)`

**Nguyên nhân:** Lỗi xảy ra trong hàm `isValidImageMime()` tại dòng 345 khi gọi `get_headers($url, 1)` với `$url` là chuỗi rỗng hoặc chỉ chứa khoảng trắng.

## Phân tích

1. **Vị trí lỗi:** `app/Services/OrderRowValidator.php:345`

    ```php
    $headers = @get_headers($url, 1); // Lỗi xảy ra ở đây khi $url rỗng
    ```

2. **Luồng gọi hàm:**

    - `validateRows()` → `validatePrintData()` → `validateImageUrl()` → `isValidImageMime()`

3. **Nguyên nhân:**
    - Trong `validatePrintData()`, code kiểm tra `!empty($row[$mockupCol])` và `!empty($row[$designCol])`
    - Nhưng sau khi `trim()`, URL có thể trở thành chuỗi rỗng
    - `validateImageUrl()` không kiểm tra URL rỗng trước khi gọi `isValidImageMime()`
    - `isValidImageMime()` gọi `get_headers()` với URL rỗng, gây lỗi "Path cannot be empty"

## Giải pháp đã áp dụng

### 1. Sửa hàm `validateImageUrl()`

**Trước:**

```php
private function validateImageUrl(string $url, int $excelRow, string $col, string $type, array &$rowErrors): void
{
    if (str_contains($url, 'drive.google.com')) {
        // ...
    } else {
        if (!$this->isValidImageMime($url)) {
            $rowErrors[] = "Row $excelRow: File at column $col is not a valid image (JPG, JPEG, PNG).";
        }
    }
}
```

**Sau:**

```php
private function validateImageUrl(string $url, int $excelRow, string $col, string $type, array &$rowErrors): void
{
    // Kiểm tra URL không rỗng
    if (empty(trim($url))) {
        $rowErrors[] = "Row $excelRow: $type URL at column $col cannot be empty.";
        return;
    }

    if (str_contains($url, 'drive.google.com')) {
        // ...
    } else {
        if (!$this->isValidImageMime($url)) {
            $rowErrors[] = "Row $excelRow: File at column $col is not a valid image (JPG, JPEG, PNG).";
        }
    }
}
```

### 2. Sửa hàm `isValidImageMime()`

**Trước:**

```php
private function isValidImageMime(string $url): bool
{
    $headers = @get_headers($url, 1);
    if (!$headers) return false;
    $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
    return in_array(strtolower($mime), $this->validImageMimeTypes);
}
```

**Sau:**

```php
private function isValidImageMime(string $url): bool
{
    // Kiểm tra URL không rỗng trước khi gọi get_headers
    if (empty(trim($url))) {
        return false;
    }

    $headers = @get_headers($url, 1);
    if (!$headers) return false;
    $mime = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : '';
    return in_array(strtolower($mime), $this->validImageMimeTypes);
}
```

### 3. Sửa logic trong `validatePrintData()`

**Trước:**

```php
if (!empty($row[$mockupCol])) {
    $hasMockup = true;
    $mockupCount++;
    $url = trim($row[$mockupCol]);
    $this->validateImageUrl($url, $excelRow, $mockupCol, 'mockup', $rowErrors);
}
```

**Sau:**

```php
if (!empty($row[$mockupCol])) {
    $url = trim($row[$mockupCol]);
    if (!empty($url)) {
        $hasMockup = true;
        $mockupCount++;
        $this->validateImageUrl($url, $excelRow, $mockupCol, 'mockup', $rowErrors);
    }
}
```

## Kết quả mong đợi

1. **Loại bỏ lỗi "Path cannot be empty":** Không còn lỗi khi URL rỗng
2. **Validation tốt hơn:** Hiển thị thông báo lỗi rõ ràng cho URL rỗng
3. **Tính ổn định:** Code xử lý tốt hơn các trường hợp edge case

## Testing

### Test case 1: URL rỗng

```php
// Input: $url = ""
// Expected: Không gọi get_headers(), trả về false hoặc thông báo lỗi
```

### Test case 2: URL chỉ chứa khoảng trắng

```php
// Input: $url = "   "
// Expected: Không gọi get_headers(), trả về false hoặc thông báo lỗi
```

### Test case 3: URL hợp lệ

```php
// Input: $url = "https://example.com/image.jpg"
// Expected: Gọi get_headers() bình thường
```

## Monitoring

Theo dõi logs để đảm bảo:

-   Không còn lỗi "Path cannot be empty"
-   Thông báo lỗi mới "URL cannot be empty" xuất hiện khi cần thiết
-   Validation hoạt động bình thường với URL hợp lệ

## Lưu ý

-   Lỗi này không liên quan đến các vấn đề upload file trước đó
-   Đây là lỗi validation trong quá trình xử lý Excel order import
-   Cần kiểm tra dữ liệu Excel để đảm bảo không có cột URL rỗng

