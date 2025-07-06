# Refactoring OrderRowValidator

## Tổng quan

Đã thực hiện refactoring để tách logic validation của các dòng order từ class `ExcelOrderImportService` thành một class riêng biệt `OrderRowValidator`.

## Thay đổi chính

### 1. Tạo class OrderRowValidator mới

**File:** `app/Services/OrderRowValidator.php`

-   Chứa toàn bộ logic validation cho các dòng order từ file Excel
-   Các method chính:
    -   `validateRows(array $rows, string $warehouse): array` - Method chính để validate
    -   `validateRequiredFields()` - Kiểm tra các trường bắt buộc
    -   `validateShippingMethodAndComment()` - Kiểm tra shipping method và comment
    -   `validateSku()` - Kiểm tra SKU
    -   `validatePrintData()` - Kiểm tra position, mockup, design
    -   `validatePosition()` - Kiểm tra position theo warehouse và product type
    -   `validateImageUrl()` - Kiểm tra URL hình ảnh
    -   `getProductTypeFromSku()` - Xác định loại sản phẩm từ SKU
    -   `getRequiredPrintCount()` - Xác định số lượng print yêu cầu từ SKU

### 2. Cập nhật ExcelOrderImportService

**File:** `app/Services/ExcelOrderImportService.php`

-   Thêm dependency injection cho `OrderRowValidator`
-   Xóa hàm `validateRows` cũ (đã được tách ra)
-   Cập nhật method `processCustomer` để sử dụng `OrderRowValidator`

### 3. Cập nhật SupplierFulfillmentController

**File:** `app/Http/Controllers/SupplierFulfillmentController.php`

-   Thêm dependency injection cho `OrderRowValidator` và `ExcelOrderImportService`
-   Cập nhật constructor để inject các service
-   Thay thế việc tạo instance trực tiếp bằng dependency injection

### 4. Đăng ký Service Container

**File:** `app/Providers/AppServiceProvider.php`

-   Đăng ký `OrderRowValidator` và `ExcelOrderImportService` trong service container
-   Sử dụng singleton pattern để đảm bảo chỉ có một instance

## Lợi ích của việc refactoring

1. **Separation of Concerns**: Logic validation được tách riêng khỏi logic xử lý import
2. **Reusability**: `OrderRowValidator` có thể được sử dụng ở nhiều nơi khác
3. **Testability**: Dễ dàng viết unit test cho validation logic
4. **Maintainability**: Code dễ đọc và bảo trì hơn
5. **Dependency Injection**: Tuân thủ nguyên tắc DI, giảm coupling

## Cách sử dụng

### Sử dụng trực tiếp

```php
use App\Services\OrderRowValidator;

$validator = new OrderRowValidator();
$errors = $validator->validateRows($rows, 'UK');
```

### Sử dụng với Dependency Injection

```php
public function __construct(OrderRowValidator $validator)
{
    $this->validator = $validator;
}

public function someMethod()
{
    $errors = $this->validator->validateRows($rows, $warehouse);
}
```

## Cấu trúc validation

### Các trường bắt buộc

-   External ID (A)
-   First Name (E)
-   Address 1 (H)
-   City (J)
-   County (K)
-   Postcode (L)
-   Country (M)
-   SKU (Q)
-   Quantity (S)

### Validation theo warehouse

-   **UK**: Position format đơn giản (Front, Back, Left sleeve, Right sleeve, Hem)
-   **US**: Position format phức tạp (size-side hoặc size-Front (Special))

### Validation theo product type

-   BabyOnesie, Magnet, Diecut-Magnet, UV Sticker, Vinyl Sticker
-   Mỗi loại có size và position hợp lệ riêng

### Validation hình ảnh

-   Hỗ trợ Google Drive links
-   Kiểm tra mimetype cho external URLs
-   Yêu cầu ít nhất một design URL

## Testing

File test đơn giản: `test_order_validator.php`

Chạy test:

```bash
php test_order_validator.php
```
