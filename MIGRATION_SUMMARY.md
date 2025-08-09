# Tóm tắt Migration Shipping Overrides

## 🎯 Mục tiêu

Chuyển đổi từ cấu trúc cũ (tier_name, user_id trong shipping_prices) sang cấu trúc mới (shipping_overrides riêng biệt).

## 📊 Cấu trúc Database

### **Trước:**

```sql
shipping_prices:
- id, variant_id, method, price, currency
- tier_name (enum)
- user_id (FK)
```

### **Sau:**

```sql
shipping_prices:
- id, variant_id, method, price, currency

shipping_overrides:
- id, shipping_price_id (FK), user_ids (JSON), tier_name, override_price, currency
```

## 🔄 Các thay đổi chính

### **1. Database Migrations**

-   ✅ `create_shipping_overrides_table.php` - Tạo bảng mới
-   ✅ `remove_tier_and_user_from_shipping_prices.php` - Xóa cột cũ
-   ✅ `migrate_shipping_data_to_overrides.php` - Migrate dữ liệu

### **2. Models**

-   ✅ `ShippingOverride.php` - Model mới với đầy đủ methods
-   ✅ `ShippingPrice.php` - Cập nhật để sử dụng overrides
-   ✅ `ProductVariant.php` - Cập nhật methods để tương thích

### **3. Services**

-   ✅ `ShippingOverrideService.php` - Service quản lý overrides
-   ✅ `UserSpecificPricingService.php` - Cập nhật để tương thích

### **4. Controllers**

-   ✅ `ShippingOverrideController.php` - API endpoints

## 🚀 Tính năng mới

### **1. Hỗ trợ nhiều user trong một override**

```php
// Thêm nhiều user vào một override
$override = ShippingOverride::create([
    'shipping_price_id' => 1,
    'user_ids' => [123, 456, 789],
    'override_price' => 12.99,
    'currency' => 'USD'
]);
```

### **2. Logic ưu tiên giá mới**

```php
$priceInfo = ShippingPrice::findPriceByPriority(
    variantId: 100,
    method: 'seller_1st',
    userId: 123,
    userTier: 'Gold'
);
// Kết quả: ['price' => 12.99, 'currency' => 'USD', 'is_override' => true]
```

### **3. Quản lý overrides linh hoạt**

```php
// Thêm user vào override
ShippingOverrideService::addUserToOverride(overrideId: 1, userId: 999);

// Xóa user khỏi override
ShippingOverrideService::removeUserFromOverride(overrideId: 1, userId: 123);
```

## 📈 Lợi ích

1. **Hiệu quả:** Giảm số lượng records trong database
2. **Linh hoạt:** Một override có thể áp dụng cho nhiều user
3. **Dễ quản lý:** Tách biệt rõ ràng giữa giá cơ bản và giá ghi đè
4. **Mở rộng:** Dễ dàng thêm các loại override mới

## 🔧 Cách sử dụng

### **Thiết lập giá riêng cho user:**

```php
use App\Services\ShippingOverrideService;

$override = ShippingOverrideService::setUserPrice(
    variantId: 100,
    method: 'seller_1st',
    userId: 123,
    price: 12.99,
    currency: 'USD'
);
```

### **Thiết lập giá riêng cho tier:**

```php
$override = ShippingOverrideService::setTierPrice(
    variantId: 100,
    method: 'seller_1st',
    tierName: 'Gold',
    price: 13.99,
    currency: 'USD'
);
```

### **Lấy giá với logic ưu tiên:**

```php
$priceInfo = ShippingPrice::findPriceByPriority(
    variantId: 100,
    method: 'seller_1st',
    userId: 123,
    userTier: 'Gold'
);
```

## ⚠️ Lưu ý quan trọng

1. **Migration:** Chạy migration theo thứ tự để tránh lỗi
2. **Backup:** Backup database trước khi chạy migration
3. **Testing:** Test kỹ các API endpoints sau khi cập nhật
4. **Documentation:** Cập nhật tài liệu cho team

## 🧪 Testing

### **Test migration:**

```bash
php artisan migrate --path=database/migrations/2025_01_25_000000_create_shipping_overrides_table.php
php artisan migrate --path=database/migrations/2025_01_25_000001_remove_tier_and_user_from_shipping_prices.php
php artisan migrate --path=database/migrations/2025_01_25_000002_migrate_shipping_data_to_overrides.php
```

### **Test rollback:**

```bash
php artisan migrate:rollback --step=3
```

## 📝 TODO

-   [ ] Test tất cả API endpoints
-   [ ] Cập nhật frontend để sử dụng cấu trúc mới
-   [ ] Tạo unit tests cho các methods mới
-   [ ] Cập nhật documentation cho team
-   [ ] Monitor performance sau khi deploy
