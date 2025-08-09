# Shipping Overrides System

Hệ thống Shipping Overrides cho phép ghi đè giá shipping cho từng user hoặc tier cụ thể một cách linh hoạt.

## 📊 Cấu trúc Database

### **1. shipping_prices** (Bảng chính)

-   **Mục đích:** Lưu giá cơ bản cho từng variant và shipping method
-   **Trường:**
    -   `id` (Primary Key)
    -   `variant_id` (FK → product_variants.id)
    -   `method` (tiktok_1st, tiktok_next, seller_1st, seller_next)
    -   `price` (Giá cơ bản)
    -   `currency` (USD, VND, GBP)

### **2. shipping_overrides** (Bảng ghi đè)

-   **Mục đích:** Ghi đè giá cho user hoặc tier cụ thể
-   **Trường:**
    -   `id` (Primary Key)
    -   `shipping_price_id` (FK → shipping_prices.id)
    -   `user_ids` (JSON array - Mảng các user_id)
    -   `tier_name` (Wood, Silver, Gold, Diamond, Special)
    -   `override_price` (Giá ghi đè)
    -   `currency` (USD, VND, GBP)

## 🔄 Quan hệ

```
product_variants (1) ←→ (N) shipping_prices (1) ←→ (N) shipping_overrides
```

## 💡 Cách hoạt động

### **Thứ tự ưu tiên giá:**

1. **User-specific override** (cao nhất) - Giá riêng cho user cụ thể
2. **Tier override** - Giá theo tier của user
3. **Base price** - Giá cơ bản từ shipping_prices

### **Ví dụ dữ liệu:**

| shipping_prices |            |             |       |
| --------------- | ---------- | ----------- | ----- | -------- |
| id              | variant_id | method      | price | currency |
| 1               | 100        | seller_1st  | 15.99 | USD      |
| 2               | 100        | seller_next | 12.99 | USD      |

| shipping_overrides |                   |            |           |                |          |
| ------------------ | ----------------- | ---------- | --------- | -------------- | -------- |
| id                 | shipping_price_id | user_ids   | tier_name | override_price | currency |
| 1                  | 1                 | [123, 456] | null      | 12.99          | USD      |
| 2                  | 1                 | null       | Gold      | 13.99          | USD      |
| 3                  | 2                 | [789]      | null      | 10.99          | USD      |

## 🚀 Cách sử dụng

### **1. Thiết lập giá riêng cho user**

```php
use App\Services\ShippingOverrideService;

// Thiết lập giá riêng cho user
$override = ShippingOverrideService::setUserPrice(
    variantId: 100,
    method: 'seller_1st',
    userId: 123,
    price: 12.99,
    currency: 'USD'
);
```

### **2. Thiết lập giá riêng cho tier**

```php
// Thiết lập giá riêng cho tier Gold
$override = ShippingOverrideService::setTierPrice(
    variantId: 100,
    method: 'seller_1st',
    tierName: 'Gold',
    price: 13.99,
    currency: 'USD'
);
```

### **3. Lấy giá với logic ưu tiên**

```php
// Lấy giá cho user với tier
$priceInfo = ShippingOverrideService::getPriceForUser(
    variantId: 100,
    method: 'seller_1st',
    userId: 123,
    userTier: 'Gold'
);

// Kết quả: ['price' => 12.99, 'currency' => 'USD', 'is_override' => true]
```

### **4. Thêm nhiều user vào một override**

```php
// Thêm user vào override hiện có
ShippingOverrideService::addUserToOverride(overrideId: 1, userId: 999);

// Xóa user khỏi override
ShippingOverrideService::removeUserFromOverride(overrideId: 1, userId: 123);
```

### **5. Quản lý overrides**

```php
// Lấy tất cả overrides cho một variant
$overrides = ShippingOverrideService::getOverridesForVariant(100);

// Lấy overrides cho một user
$userOverrides = ShippingOverrideService::getOverridesForUser(123);

// Lấy overrides cho một tier
$tierOverrides = ShippingOverrideService::getOverridesForTier('Gold');
```

## 🔧 Migration

### **Chạy migration:**

```bash
php artisan migrate
```

### **Rollback migration:**

```bash
php artisan migrate:rollback --step=3
```

## 📈 Lợi ích của cấu trúc mới

1. **Linh hoạt:** Một override có thể áp dụng cho nhiều user
2. **Hiệu quả:** Giảm số lượng records trong database
3. **Dễ quản lý:** Tách biệt rõ ràng giữa giá cơ bản và giá ghi đè
4. **Mở rộng:** Dễ dàng thêm các loại override mới trong tương lai

## 🛠️ API Endpoints (Gợi ý)

```php
// GET /api/shipping-overrides/variant/{variantId}
// POST /api/shipping-overrides/user
// POST /api/shipping-overrides/tier
// DELETE /api/shipping-overrides/{id}
// PUT /api/shipping-overrides/{id}/users
```

## 🔍 Query Examples

### **Tìm override cho user:**

```sql
SELECT * FROM shipping_overrides
WHERE shipping_price_id = 1
AND JSON_CONTAINS(user_ids, '123');
```

### **Tìm override cho tier:**

```sql
SELECT * FROM shipping_overrides
WHERE shipping_price_id = 1
AND tier_name = 'Gold';
```

### **Lấy giá với logic ưu tiên:**

```php
// Sử dụng method findPriceByPriority trong ShippingPrice model
$priceInfo = ShippingPrice::findPriceByPriority(
    variantId: 100,
    method: 'seller_1st',
    userId: 123,
    userTier: 'Gold'
);
```
