# 🏆 Hệ Thống Giá Theo Tier

## 📋 Mô tả

Hệ thống giá theo tier cho phép áp dụng mức giá khác nhau cho từng variant sản phẩm dựa trên tier của khách hàng. Hệ thống tự động tính toán giá giảm dựa trên tier và áp dụng cho các đơn hàng.

## 🎯 Cấu trúc Tier

| Tier        | Số đơn hàng / tháng | Giảm giá | Mô tả           |
| ----------- | ------------------- | -------- | --------------- |
| **Diamond** | ≥ 9,000 đơn         | 15%      | Tier cao nhất   |
| **Gold**    | ≥ 4,500 đơn         | 10%      | Tier cao        |
| **Silver**  | ≥ 1,500 đơn         | 5%       | Tier trung bình |
| **Wood**    | < 1,500 đơn         | 0%       | Tier cơ bản     |

## 🗄️ Cấu trúc Database

### Bảng `variant_tier_prices`

```sql
CREATE TABLE variant_tier_prices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id BIGINT NOT NULL,
    tier ENUM('Diamond', 'Gold', 'Silver', 'Wood') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    currency ENUM('USD', 'GBP', 'VND') DEFAULT 'USD',
    method ENUM('tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    UNIQUE KEY variant_tier_method_unique (variant_id, tier, method),
    INDEX idx_variant_tier (variant_id, tier),
    INDEX idx_tier_method (tier, method),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
);
```

## 🚀 Cài đặt

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Tạo giá theo tier từ giá gốc

```bash
# Tạo giá theo tier cho tất cả variant
php artisan tier:generate-prices

# Tạo giá theo tier với custom discounts
php artisan tier:generate-prices --custom-discounts='{"Diamond":0.20,"Gold":0.15,"Silver":0.08}'

# Tạo giá theo tier cho variant cụ thể
php artisan tier:generate-prices --variant-id=123

# Bắt buộc tạo lại tất cả giá
php artisan tier:generate-prices --force
```

### 3. Test hệ thống

```bash
php test_tier_pricing_system.php
```

## 📊 Sử dụng

### 1. Lấy giá theo tier cho user

```php
use App\Models\VariantTierPrice;
use App\Models\ProductVariant;

// Lấy giá theo tier cho user
$tierPrice = VariantTierPrice::getPriceForUser(
    $variantId,
    $userId,
    'seller_1st'
);

if ($tierPrice) {
    echo "Giá: \${$tierPrice->price} {$tierPrice->currency}";
    echo "Tier: {$tierPrice->tier}";
}
```

### 2. Sử dụng trong ProductVariant

```php
$variant = ProductVariant::find($variantId);

// Lấy giá với user (sẽ tự động áp dụng tier)
$price = $variant->getFirstItemPrice(null, $userId);

// Lấy thông tin đơn hàng với tier
$orderInfo = $variant->getOrderPriceInfo(null, 1, $userId);

// Lấy tất cả giá theo tier
$allTierPrices = $variant->getAllTierPrices();
```

### 3. Sử dụng Service

```php
use App\Services\VariantTierPriceService;

$tierPriceService = app(VariantTierPriceService::class);

// Tạo giá theo tier
$result = $tierPriceService->generateTierPricesFromShippingPrices();

// Cập nhật giá theo tier cho variant
$tierPrices = [
    [
        'tier' => 'Diamond',
        'method' => 'seller_1st',
        'price' => 15.00,
        'currency' => 'USD'
    ],
    // ...
];
$result = $tierPriceService->updateVariantTierPrices($variantId, $tierPrices);

// Lấy thống kê
$stats = $tierPriceService->getTierPriceStatistics();
```

## 🔧 API Endpoints

### 1. Lấy giá theo tier cho user

```http
GET /api/variants/{variantId}/tier-price
Authorization: Bearer {token}
```

Response:

```json
{
    "success": true,
    "data": {
        "variant_id": 123,
        "user_id": 456,
        "tier": "Gold",
        "method": "seller_1st",
        "price": 13.5,
        "currency": "USD",
        "original_price": 15.0,
        "discount_rate": 0.1
    }
}
```

### 2. Cập nhật giá theo tier

```http
PUT /api/variants/{variantId}/tier-prices
Authorization: Bearer {token}
Content-Type: application/json

{
    "tier_prices": [
        {
            "tier": "Diamond",
            "method": "seller_1st",
            "price": 12.75,
            "currency": "USD"
        }
    ]
}
```

### 3. Lấy thống kê giá theo tier

```http
GET /api/tier-prices/statistics
Authorization: Bearer {token}
```

## 📈 Thống kê

### 1. Tỷ lệ bao phủ

-   **Tổng số variant**: Số lượng variant trong hệ thống
-   **Variant có giá theo tier**: Số variant đã có giá theo tier
-   **Tỷ lệ bao phủ**: Phần trăm variant có giá theo tier

### 2. Thống kê theo tier

-   **Số variant**: Số lượng variant có giá cho mỗi tier
-   **Giá trung bình**: Giá trung bình cho mỗi tier
-   **Giá min/max**: Giá thấp nhất và cao nhất

## 🔄 Tích hợp với hệ thống hiện tại

### 1. ExcelOrderImportService

Hệ thống sẽ tự động áp dụng giá theo tier khi import đơn hàng:

```php
// Trong ExcelOrderImportService
$variant = ProductVariant::find($variantId);
$priceInfo = $variant->getOrderPriceInfo($shippingMethod, $position, $userId);
$printPrice = $priceInfo['print_price'];
```

### 2. SupplierFulfillmentController

Khi tạo đơn hàng qua API, hệ thống sẽ tự động áp dụng giá theo tier:

```php
// Trong SupplierFulfillmentController
$variant = ProductVariant::find($variantId);
$priceInfo = $variant->getOrderPriceInfo($shippingMethod, 1, $userId);
$printPrice = $priceInfo['print_price'];
```

## 🛠️ Quản lý

### 1. Tạo giá theo tier thủ công

```php
use App\Models\VariantTierPrice;

// Tạo hoặc cập nhật giá
VariantTierPrice::createOrUpdatePrice(
    $variantId,
    'Diamond',
    'seller_1st',
    12.75,
    'USD'
);
```

### 2. Xóa giá theo tier

```php
use App\Services\VariantTierPriceService;

$tierPriceService = app(VariantTierPriceService::class);

// Xóa tất cả giá theo tier của variant
$result = $tierPriceService->deleteVariantTierPrices($variantId);

// Xóa giá theo tier cụ thể
$result = $tierPriceService->deleteVariantTierPrices($variantId, 'Diamond', 'seller_1st');
```

### 3. Kiểm tra variant chưa có giá theo tier

```php
$variantsWithoutTierPrices = $tierPriceService->getVariantsWithoutTierPrices();
echo "Có {$variantsWithoutTierPrices['count']} variant chưa có giá theo tier";
```

## 🔍 Debug và Troubleshooting

### 1. Kiểm tra giá theo tier

```php
// Kiểm tra giá theo tier cho variant
$tierPrices = VariantTierPrice::where('variant_id', $variantId)->get();
foreach ($tierPrices as $tierPrice) {
    echo "{$tierPrice->tier} ({$tierPrice->method}): \${$tierPrice->price}\n";
}
```

### 2. Kiểm tra tier của user

```php
use App\Models\UserTier;

$userTier = UserTier::getCurrentTier($userId);
if ($userTier) {
    echo "User tier: {$userTier->tier}\n";
    echo "Order count: {$userTier->order_count}\n";
}
```

### 3. Log và monitoring

Hệ thống tự động log các hoạt động:

```php
// Trong logs/laravel.log
[2024-01-15 10:30:00] local.INFO: Found tier price for variant {"variant_id":123,"user_id":456,"method":"seller_1st","price_usd":13.50,"tier":"Gold"}
```

## 📝 Lưu ý quan trọng

1. **Tier được tính dựa trên số đơn hàng tháng trước**
2. **Giá theo tier có hiệu lực ngay lập tức khi tier thay đổi**
3. **Nếu không tìm thấy giá theo tier, hệ thống sẽ fallback về giá gốc**
4. **Giá theo tier được lưu riêng biệt cho từng method (tiktok_1st, seller_1st, etc.)**
5. **Hệ thống hỗ trợ 3 loại tiền tệ: USD, GBP, VND**

## 🚀 Performance

-   **Index**: Đã tạo index cho các trường thường query
-   **Caching**: Có thể cache tier của user để tăng performance
-   **Batch processing**: Hỗ trợ xử lý hàng loạt khi tạo giá theo tier

## 🔒 Security

-   **Validation**: Tất cả input đều được validate
-   **Authorization**: API endpoints yêu cầu authentication
-   **Audit trail**: Log đầy đủ các thay đổi giá theo tier

```

```
