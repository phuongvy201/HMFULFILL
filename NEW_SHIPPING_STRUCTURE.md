# 🚚 Cấu trúc Shipping Pricing Mới

## 🎯 Tổng quan

Hệ thống shipping pricing đã được refactor để tách biệt rõ ràng giữa giá mặc định và giá override.

## 📊 Cấu trúc Database

### **Bảng `shipping_prices`**

-   Lưu giá **mặc định** (Wood tier)
-   Cột: `variant_id`, `method`, `price`, `currency`

### **Bảng `shipping_overrides`**

-   Lưu giá **override** cho tier và user
-   Cột: `shipping_price_id`, `user_ids` (JSON), `tier_name`, `override_price`, `currency`

## 📋 Logic Import Excel

### **File Excel chính (`product_import.xlsx`)**

| Cột           | Mô tả         | Lưu vào                 |
| ------------- | ------------- | ----------------------- |
| T-W (19-22)   | **Wood tier** | `shipping_prices.price` |
| X-AA (23-26)  | Silver tier   | `shipping_overrides`    |
| AB-AE (27-30) | Gold tier     | `shipping_overrides`    |
| AF-AI (31-34) | Diamond tier  | `shipping_overrides`    |
| AJ-AM (35-38) | Special tier  | `shipping_overrides`    |

### **Ví dụ dữ liệu:**

```
Cột T (19): Wood - tiktok_1st = 12.50 → shipping_prices.price = 12.50
Cột X (23): Silver - tiktok_1st = 15.00 → shipping_overrides.override_price = 15.00
Cột AB (27): Gold - tiktok_1st = 18.00 → shipping_overrides.override_price = 18.00
```

## 🎯 Logic Ưu tiên Giá

### **Thứ tự ưu tiên:**

1. **User-specific price** (cao nhất)

    - Từ `shipping_overrides` với `user_ids` chứa user ID
    - Import qua file `user_pricing_import.xlsx`

2. **Tier-specific price** (Silver, Gold, Diamond, Special)

    - Từ `shipping_overrides` với `tier_name`
    - Import từ cột X-AM trong file chính

3. **Base price** (Wood tier)
    - Từ `shipping_prices.price`
    - Import từ cột T-W trong file chính

## 🔧 Code Logic

### **Import sản phẩm:**

```php
// Tạo shipping price với giá Wood tier
$shippingPrice = ShippingPrice::create([
    'variant_id' => $variant->id,
    'method' => $method,
    'price' => $woodPrice, // Giá từ cột T-W
    'currency' => $currency
]);

// Tạo overrides cho các tier khác
foreach ($otherTierConfigs as $tierName => $config) {
    if (!empty($cells[$colIndex])) {
        ShippingOverride::create([
            'shipping_price_id' => $shippingPrice->id,
            'tier_name' => $tierName,
            'override_price' => (float)($cells[$colIndex]),
            'currency' => $currency
        ]);
    }
}
```

### **Lấy giá theo ưu tiên:**

```php
public static function findPriceByPriority($variantId, $method, $userId = null, $userTier = null)
{
    // 1. User-specific price
    if ($userId) {
        $userOverride = ShippingOverride::findForUser($shippingPriceId, $userId);
        if ($userOverride) return $userOverride->override_price;
    }

    // 2. Tier-specific price
    if ($userTier) {
        $tierOverride = ShippingOverride::findForTier($shippingPriceId, $userTier);
        if ($tierOverride) return $tierOverride->override_price;
    }

    // 3. Base price (Wood tier)
    return $basePrice->price;
}
```

## 📈 Lợi ích

1. **Rõ ràng:** Wood tier = giá mặc định, các tier khác = override
2. **Hiệu quả:** Không cần lưu Wood tier trong overrides
3. **Linh hoạt:** User có thể có giá riêng cho bất kỳ tier nào
4. **Dễ quản lý:** Tách biệt rõ ràng giữa giá cơ bản và giá đặc biệt

## 🔄 Migration

### **Bước 1:** Tạo bảng `shipping_overrides`

```bash
php artisan migrate --path=database/migrations/2025_01_25_000000_create_shipping_overrides_table.php
```

### **Bước 2:** Xóa cột cũ từ `shipping_prices`

```bash
php artisan migrate --path=database/migrations/2025_01_25_000001_remove_tier_and_user_from_shipping_prices.php
```

### **Bước 3:** Migrate dữ liệu cũ (nếu có)

```bash
php artisan migrate --path=database/migrations/2025_01_25_000002_migrate_shipping_data_to_overrides.php
```

## 🎯 Sử dụng

### **Import sản phẩm:**

1. Chuẩn bị file Excel theo cấu trúc mới
2. Cột T-W: Giá Wood tier (giá mặc định)
3. Cột X-AM: Giá các tier khác (override)

### **Import user pricing:**

1. Sử dụng file `user_pricing_import.xlsx` riêng
2. Chỉ định User ID, Variant SKU, Method, Price
3. Hệ thống sẽ tạo override cho user cụ thể

## ⚠️ Lưu ý

-   **Wood tier** không được lưu trong `shipping_overrides`
-   **User pricing** luôn có ưu tiên cao nhất
-   **Tier pricing** chỉ áp dụng khi user không có giá riêng
-   **Base price** (Wood) chỉ áp dụng khi không có override nào
