# Hướng dẫn xóa Shipping Prices

## Tổng quan

Hệ thống cung cấp các công cụ an toàn để xóa shipping prices theo nhiều tiêu chí khác nhau. Tất cả các thao tác đều có backup và validation để đảm bảo an toàn.

## Các Command có sẵn

### 1. Xóa Shipping Prices theo User ID

```bash
# Xem preview shipping prices của user_id=18
php artisan shipping:cleanup --user-id=18 --dry-run

# Xóa tất cả shipping prices của user_id=18
php artisan shipping:cleanup --user-id=18

# Xóa với force (không cần xác nhận)
php artisan shipping:cleanup --user-id=18 --force
```

### 2. Xóa Shipping Prices theo Variant ID

```bash
# Xem preview shipping prices của variant_id=123
php artisan shipping:cleanup --variant-id=123 --dry-run

# Xóa tất cả shipping prices của variant_id=123
php artisan shipping:cleanup --variant-id=123
```

### 3. Xóa Shipping Prices theo Method

```bash
# Xem preview shipping prices của method tiktok_1st
php artisan shipping:cleanup --method=tiktok_1st --dry-run

# Xóa tất cả shipping prices của method tiktok_1st
php artisan shipping:cleanup --method=tiktok_1st
```

### 4. Xóa Shipping Prices cũ

```bash
# Xem preview shipping prices cũ hơn 30 ngày
php artisan shipping:cleanup --older-than=30 --dry-run

# Xóa shipping prices cũ hơn 30 ngày
php artisan shipping:cleanup --older-than=30
```

## Sử dụng Service trực tiếp

### 1. Xóa theo User ID

```php
use App\Services\ShippingPriceCleanupService;

// Xóa tất cả shipping prices của user_id=18
$result = ShippingPriceCleanupService::deleteByUserId(18);

if ($result['success']) {
    echo "Đã xóa {$result['deleted_count']} shipping prices";
} else {
    echo "Lỗi: " . $result['error'];
}
```

### 2. Xóa với điều kiện tùy chỉnh

```php
// Xóa shipping prices với nhiều điều kiện
$criteria = [
    'user_id' => 18,
    'method' => 'tiktok_1st',
    'currency' => 'USD'
];

$result = ShippingPriceCleanupService::deleteWithCriteria($criteria);
```

### 3. Tìm shipping prices trước khi xóa

```php
// Tìm shipping prices của user_id=18
$result = ShippingPriceCleanupService::findShippingPrices(['user_id' => 18]);

echo "Tìm thấy {$result['count']} shipping prices";

foreach ($result['shipping_prices'] as $price) {
    echo "ID: {$price->id}, Method: {$price->method}, Price: {$price->price}";
}
```

## Ví dụ cụ thể cho user_id=18

### Bước 1: Xem thông tin user

```bash
# Kiểm tra user có tồn tại không
php artisan tinker
>>> $user = App\Models\User::find(18);
>>> echo $user ? $user->email : "User not found";
```

### Bước 2: Xem preview shipping prices

```bash
# Xem tất cả shipping prices của user_id=18
php artisan shipping:cleanup --user-id=18 --dry-run
```

### Bước 3: Backup trước khi xóa

```php
use App\Services\ShippingPriceCleanupService;

// Backup shipping prices của user_id=18
$result = ShippingPriceCleanupService::findShippingPrices(['user_id' => 18]);
$backup = ShippingPriceCleanupService::backupShippingPrices($result['shipping_prices']);

echo "Backup đã được tạo: " . $backup['filename'];
```

### Bước 4: Xóa shipping prices

```bash
# Xóa với xác nhận
php artisan shipping:cleanup --user-id=18

# Hoặc xóa với force
php artisan shipping:cleanup --user-id=18 --force
```

## Các loại Shipping Prices có thể xóa

### 1. User-specific prices

-   Có `user_id` không null
-   Giá riêng cho từng user
-   An toàn để xóa nếu user không còn cần

### 2. Tier prices

-   Có `tier_name` (Wood, Silver, Gold, etc.)
-   Giá theo tier của user
-   Cần cẩn thận khi xóa

### 3. Default prices

-   `user_id` và `tier_name` đều null
-   Giá mặc định
-   Không nên xóa trừ khi có lý do cụ thể

### 4. Old prices

-   Tạo cách đây hơn X ngày
-   Có thể xóa để dọn dẹp

## Quy trình xóa an toàn

### Bước 1: Phân tích

```bash
# Xem thống kê shipping prices
php artisan tinker
>>> $stats = App\Services\ShippingPriceCleanupService::getStatistics();
>>> print_r($stats);
```

### Bước 2: Tìm shipping prices

```bash
# Xem preview trước khi xóa
php artisan shipping:cleanup --user-id=18 --dry-run
```

### Bước 3: Backup

```php
// Backup tự động hoặc thủ công
$result = ShippingPriceCleanupService::findShippingPrices(['user_id' => 18]);
ShippingPriceCleanupService::backupShippingPrices($result['shipping_prices']);
```

### Bước 4: Xóa

```bash
# Xóa với xác nhận
php artisan shipping:cleanup --user-id=18
```

## Monitoring và Logging

### Log Files

-   Tất cả thao tác xóa được log
-   Log location: `storage/logs/laravel.log`
-   Format: JSON với đầy đủ thông tin

### Monitoring

```bash
# Xem log gần đây
tail -f storage/logs/laravel.log | grep "Shipping price deleted"

# Xem backup files
ls -la storage/app/backups/shipping_prices/
```

## Troubleshooting

### Lỗi thường gặp

1. **"User not found"**

    - Kiểm tra user ID có tồn tại không
    - Chạy `php artisan tinker` để kiểm tra

2. **"No shipping prices found"**

    - User không có shipping prices nào
    - Kiểm tra lại user_id

3. **"Permission denied"**
    - Kiểm tra quyền ghi thư mục `storage/app/backups/`
    - Tạo thư mục nếu chưa có

### Tips

1. **Luôn dùng dry-run trước**

    ```bash
    php artisan shipping:cleanup --user-id=18 --dry-run
    ```

2. **Backup thủ công trước khi xóa quan trọng**

    ```php
    $result = ShippingPriceCleanupService::findShippingPrices(['user_id' => 18]);
    ShippingPriceCleanupService::backupShippingPrices($result['shipping_prices']);
    ```

3. **Kiểm tra dependencies**
    ```bash
    php artisan shipping:cleanup --user-id=18 --dry-run
    ```

## Ví dụ thực tế

### Scenario 1: Xóa shipping prices của user test

```bash
# Tìm user test
php artisan tinker
>>> $user = App\Models\User::where('email', 'like', '%test%')->first();
>>> echo $user ? $user->id : "No test user found";

# Xóa shipping prices của user test
php artisan shipping:cleanup --user-id=18 --dry-run
php artisan shipping:cleanup --user-id=18
```

### Scenario 2: Xóa shipping prices cũ

```bash
# Xóa shipping prices cũ hơn 90 ngày
php artisan shipping:cleanup --older-than=90 --dry-run
php artisan shipping:cleanup --older-than=90
```

### Scenario 3: Xóa shipping prices theo method

```bash
# Xóa tất cả shipping prices của method tiktok_1st
php artisan shipping:cleanup --method=tiktok_1st --dry-run
php artisan shipping:cleanup --method=tiktok_1st
```

## Performance

### Optimization

-   Sử dụng batch processing cho số lượng lớn
-   Index database cho các trường thường query
-   Cleanup backup files cũ định kỳ

### Monitoring

-   Theo dõi số lượng shipping prices
-   Kiểm tra performance sau khi xóa
-   Backup và restore test định kỳ

## Best Practices

1. **Luôn backup trước khi xóa**
2. **Dùng dry-run để preview**
3. **Kiểm tra dependencies kỹ lưỡng**
4. **Log đầy đủ các thao tác**
5. **Test restore procedure định kỳ**
6. **Monitor performance sau cleanup**
