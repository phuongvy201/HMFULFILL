# Hướng dẫn xóa Product Variants

## Tổng quan

Hệ thống cung cấp các công cụ an toàn để xóa product variants cũ hoặc không sử dụng. Tất cả các thao tác đều có backup và validation để đảm bảo an toàn.

## Các Command có sẵn

### 1. Phân tích Variants

```bash
# Phân tích tổng quan
php artisan variants:analyze

# Phân tích variants không sử dụng
php artisan variants:analyze --unused

# Phân tích variants orphaned (không có product)
php artisan variants:analyze --orphaned

# Phân tích SKU trùng lặp
php artisan variants:analyze --duplicates

# Phân tích cho product cụ thể
php artisan variants:analyze --product-id=123
```

### 2. Xóa Variants

```bash
# Xem preview variants sẽ bị xóa (dry-run)
php artisan variants:cleanup --unused --dry-run

# Xóa variants không sử dụng
php artisan variants:cleanup --unused

# Xóa variants cũ hơn 30 ngày
php artisan variants:cleanup --older-than=30

# Xóa variant theo SKU cụ thể
php artisan variants:cleanup --sku=SKU123

# Xóa variants của product cụ thể
php artisan variants:cleanup --product-id=123

# Xóa với force (không cần xác nhận)
php artisan variants:cleanup --unused --force
```

## Các loại Variants có thể xóa

### 1. Variants không sử dụng

-   Không có shipping prices
-   Không được sử dụng trong orders
-   An toàn nhất để xóa

### 2. Variants cũ

-   Tạo cách đây hơn X ngày
-   Cần kiểm tra kỹ trước khi xóa

### 3. Variants orphaned

-   Không có product liên quan
-   Có thể xóa an toàn

### 4. Variants trùng lặp

-   Có SKU giống nhau
-   Cần review và merge

## Quy trình xóa an toàn

### Bước 1: Phân tích

```bash
# Xem tổng quan
php artisan variants:analyze

# Xem variants không sử dụng
php artisan variants:analyze --unused
```

### Bước 2: Preview

```bash
# Xem preview trước khi xóa
php artisan variants:cleanup --unused --dry-run
```

### Bước 3: Backup (tự động)

-   Hệ thống tự động backup trước khi xóa
-   Backup được lưu trong `storage/app/backups/variants/`

### Bước 4: Xóa

```bash
# Xóa với xác nhận
php artisan variants:cleanup --unused

# Hoặc xóa với force
php artisan variants:cleanup --unused --force
```

## Backup và Restore

### Backup tự động

-   Mỗi variant được backup trước khi xóa
-   Backup bao gồm: variant, attributes, shipping prices
-   Format: JSON file

### Restore từ backup

```php
use App\Services\VariantCleanupService;

// Restore variant
$result = VariantCleanupService::restoreVariant('variant_backup_123_2024-01-15_10-30-00.json');

// Xem danh sách backup
$backups = VariantCleanupService::getBackupFiles();
```

## Validation và Safety Checks

### Kiểm tra an toàn

-   Variant có shipping prices không?
-   Variant có được sử dụng trong orders không?
-   Variant có product liên quan không?

### Transaction Safety

-   Tất cả thao tác xóa đều trong transaction
-   Rollback tự động nếu có lỗi
-   Log đầy đủ các thao tác

## Ví dụ thực tế

### Scenario 1: Xóa variants test cũ

```bash
# Phân tích variants test
php artisan variants:analyze --product-id=123

# Xem preview
php artisan variants:cleanup --product-id=123 --older-than=7 --dry-run

# Xóa variants test cũ hơn 7 ngày
php artisan variants:cleanup --product-id=123 --older-than=7
```

### Scenario 2: Dọn dẹp variants không sử dụng

```bash
# Tìm variants không sử dụng
php artisan variants:analyze --unused

# Xóa variants không sử dụng
php artisan variants:cleanup --unused
```

### Scenario 3: Xử lý SKU trùng lặp

```bash
# Tìm SKU trùng lặp
php artisan variants:analyze --duplicates

# Xóa variant trùng lặp cụ thể
php artisan variants:cleanup --sku=DUPLICATE-SKU
```

## Monitoring và Logging

### Log Files

-   Tất cả thao tác xóa được log
-   Log location: `storage/logs/laravel.log`
-   Format: JSON với đầy đủ thông tin

### Monitoring

```bash
# Xem log gần đây
tail -f storage/logs/laravel.log | grep "Variant deleted"

# Xem backup files
ls -la storage/app/backups/variants/
```

## Troubleshooting

### Lỗi thường gặp

1. **"Variant not found"**

    - Kiểm tra variant ID/SKU có tồn tại không
    - Chạy `php artisan variants:analyze` để xem danh sách

2. **"Cannot delete variant with shipping prices"**

    - Variant có shipping prices, không an toàn để xóa
    - Cần xóa shipping prices trước hoặc dùng force

3. **"Backup failed"**
    - Kiểm tra quyền ghi thư mục `storage/app/backups/`
    - Tạo thư mục nếu chưa có

### Tips

1. **Luôn dùng dry-run trước**

    ```bash
    php artisan variants:cleanup --unused --dry-run
    ```

2. **Backup thủ công trước khi xóa quan trọng**

    ```php
    $variant = ProductVariant::find(123);
    VariantCleanupService::backupVariant($variant);
    ```

3. **Kiểm tra dependencies**
    ```bash
    php artisan variants:analyze --unused
    ```

## Performance

### Optimization

-   Sử dụng batch processing cho số lượng lớn
-   Index database cho các trường thường query
-   Cleanup backup files cũ định kỳ

### Monitoring

-   Theo dõi số lượng variants
-   Kiểm tra performance sau khi xóa
-   Backup và restore test định kỳ

## Best Practices

1. **Luôn backup trước khi xóa**
2. **Dùng dry-run để preview**
3. **Kiểm tra dependencies kỹ lưỡng**
4. **Log đầy đủ các thao tác**
5. **Test restore procedure định kỳ**
6. **Monitor performance sau cleanup**
