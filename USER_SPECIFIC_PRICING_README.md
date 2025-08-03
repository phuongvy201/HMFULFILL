# User-Specific Pricing System

Hệ thống này cho phép thiết lập giá riêng cho từng user đặc biệt mà không cần sử dụng tier system.

## Cách hoạt động

### Thứ tự ưu tiên giá:

1. **User-specific price** (cao nhất) - Giá riêng cho user cụ thể
2. **Tier price** - Giá theo tier của user (Diamond, Gold, Silver, Wood)
3. **Default price** - Giá mặc định (tier_name = null, user_id = null)
4. **Wood tier fallback** - Giá Wood tier làm fallback cuối cùng

### Cấu trúc Database

Bảng `shipping_prices` đã được mở rộng với trường `user_id`:

```sql
ALTER TABLE shipping_prices ADD COLUMN user_id BIGINT UNSIGNED NULL;
ALTER TABLE shipping_prices ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
```

## Cách sử dụng

### 1. Thiết lập giá riêng cho user

```php
use App\Services\UserSpecificPricingService;

// Thiết lập giá riêng cho user
$shippingPrice = UserSpecificPricingService::setUserPrice(
    userId: 123,
    variantId: 456,
    method: 'seller_1st',
    price: 15.99,
    currency: 'USD'
);
```

### 2. Lấy giá theo thứ tự ưu tiên

```php
use App\Models\ShippingPrice;

// Lấy giá với logic ưu tiên
$shippingPrice = ShippingPrice::findPriceByPriority(
    variantId: 456,
    method: 'seller_1st',
    userId: 123,
    userTier: 'Gold'
);
```

### 3. Sử dụng trong ProductVariant

```php
use App\Models\ProductVariant;

$variant = ProductVariant::find(456);

// Lấy giá cho order với user cụ thể
$priceInfo = $variant->getOrderPriceInfo(
    shippingMethod: 'seller_label',
    position: 1,
    userId: 123
);

// Lấy giá item đầu tiên
$firstItemPrice = $variant->getFirstItemPrice(
    shippingMethod: 'seller_label',
    userId: 123
);
```

### 4. Quản lý giá riêng cho user

```php
// Thiết lập giá riêng
$variant->setUserSpecificPrice(123, 'seller_1st', 15.99, 'USD');

// Lấy giá riêng
$userPrice = $variant->getUserSpecificPrice(123, 'seller_1st');

// Xóa giá riêng
$variant->removeUserSpecificPrice(123, 'seller_1st');

// Lấy tất cả giá riêng của user
$allPrices = $variant->getAllUserSpecificPrices(123);
```

## API Endpoints

### Admin Routes

```php
// Danh sách user có giá riêng
GET /admin/user-specific-pricing

// Form tạo giá riêng
GET /admin/user-specific-pricing/create

// Tạo giá riêng
POST /admin/user-specific-pricing

// Chi tiết giá riêng của user
GET /admin/user-specific-pricing/{userId}

// Form chỉnh sửa
GET /admin/user-specific-pricing/{userId}/{variantId}/{method}/edit

// Cập nhật giá riêng
PUT /admin/user-specific-pricing/{userId}/{variantId}/{method}

// Xóa giá riêng
DELETE /admin/user-specific-pricing/{userId}/{variantId}/{method}

// Copy giá từ user này sang user khác
POST /admin/user-specific-pricing/copy
```

### API Routes

```php
// Lấy giá riêng của user
GET /api/user-specific-pricing/{userId}/{variantId}/{method}

// Lấy tất cả giá riêng của user
GET /api/user-specific-pricing/{userId}
```

## Ví dụ sử dụng

### Ví dụ 1: Thiết lập giá riêng cho VIP customer

```php
// User ID 123 là VIP customer, được giảm giá 20%
$variant = ProductVariant::find(456);
$originalPrice = 20.00;
$vipPrice = $originalPrice * 0.8; // Giảm 20%

$variant->setUserSpecificPrice(123, 'seller_1st', $vipPrice, 'USD');
```

### Ví dụ 2: Thiết lập giá riêng cho bulk buyer

```php
// User ID 456 mua số lượng lớn, được giá tốt hơn
$bulkPrices = [
    'seller_1st' => 12.50,
    'seller_next' => 8.75,
    'tiktok_1st' => 14.25,
    'tiktok_next' => 9.50
];

foreach ($bulkPrices as $method => $price) {
    $variant->setUserSpecificPrice(456, $method, $price, 'USD');
}
```

### Ví dụ 3: Copy giá từ user này sang user khác

```php
// Copy tất cả giá từ user 123 sang user 789
$copiedCount = UserSpecificPricingService::copyUserPrices(123, 789);
echo "Đã copy {$copiedCount} giá";
```

## Lợi ích

1. **Linh hoạt**: Có thể thiết lập giá riêng cho từng user mà không cần tạo tier mới
2. **Ưu tiên cao**: User-specific price có ưu tiên cao hơn tier price
3. **Dễ quản lý**: Có thể copy, edit, delete giá riêng dễ dàng
4. **Backward compatible**: Không ảnh hưởng đến hệ thống tier hiện tại
5. **Scalable**: Có thể mở rộng cho nhiều user đặc biệt

## Lưu ý

-   User-specific price sẽ override tất cả các loại giá khác
-   Khi xóa user, tất cả giá riêng của user đó sẽ bị xóa theo (CASCADE)
-   Có thể có nhiều giá cho cùng một user (khác method, variant)
-   Hệ thống logging sẽ ghi lại tất cả thao tác tạo/sửa/xóa giá riêng
