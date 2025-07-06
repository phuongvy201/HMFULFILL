# Hướng dẫn sử dụng các hàm User Tier

## Tổng quan

Các hàm này được thiết kế để quản lý và hiển thị thông tin tier của khách hàng trong hệ thống.

## Các hàm chính

### 1. `UserTier::getCustomersWithCurrentTier($search, $tierFilter)`

**Mục đích:** Lấy danh sách khách hàng kèm thông tin tier hiện tại

**Tham số:**

-   `$search` (string, optional): Từ khóa tìm kiếm theo tên hoặc email
-   `$tierFilter` (string, optional): Lọc theo tier cụ thể (Diamond, Gold, Silver, Wood)

**Trả về:** Collection chứa thông tin khách hàng với các trường:

-   `id`: ID khách hàng
-   `name`: Tên khách hàng
-   `email`: Email khách hàng
-   `current_tier`: Tier hiện tại (text)
-   `current_tier_formatted`: Tier hiện tại (HTML badge)
-   `current_order_count`: Số đơn hàng tháng hiện tại
-   `previous_month_order_count`: Số đơn hàng tháng trước
-   `effective_month`: Tháng áp dụng tier (format: Y-m)
-   `updated_at`: Ngày cập nhật tier (format: d/m/Y H:i)
-   `actions`: Các URL hành động

**Ví dụ sử dụng:**

```php
// Lấy tất cả khách hàng
$customers = UserTier::getCustomersWithCurrentTier();

// Tìm kiếm theo tên
$customers = UserTier::getCustomersWithCurrentTier('Nguyễn Văn A');

// Lọc theo tier
$customers = UserTier::getCustomersWithCurrentTier(null, 'Diamond');

// Tìm kiếm và lọc
$customers = UserTier::getCustomersWithCurrentTier('gmail.com', 'Gold');
```

### 2. `UserTier::getTierOverview()`

**Mục đích:** Lấy thống kê tổng quan về tier

**Trả về:** Array chứa thống kê:

-   `total_customers`: Tổng số khách hàng
-   `customers_with_tier`: Số khách hàng có tier
-   `customers_without_tier`: Số khách hàng chưa có tier
-   `tier_distribution`: Phân bố theo từng tier
-   `total_orders`: Tổng số đơn hàng
-   `average_orders_per_customer`: Trung bình đơn hàng/khách hàng

**Ví dụ sử dụng:**

```php
$overview = UserTier::getTierOverview();
echo "Tổng khách hàng: " . $overview['total_customers'];
echo "Khách hàng Diamond: " . $overview['tier_distribution']['Diamond']['count'];
```

### 3. `UserTier::formatTierBadge($tier)`

**Mục đích:** Format tier thành HTML badge với màu sắc

**Tham số:**

-   `$tier` (string): Tên tier

**Trả về:** HTML string

**Ví dụ sử dụng:**

```php
echo UserTier::formatTierBadge('Diamond');
// Output: <span class="badge badge-primary">Diamond</span>
```

### 4. `UserTier::getTierColor($tier)`

**Mục đích:** Lấy mã màu hex cho tier

**Tham số:**

-   `$tier` (string): Tên tier

**Trả về:** Mã màu hex

**Ví dụ sử dụng:**

```php
$color = UserTier::getTierColor('Gold'); // Returns: #ffc107
```

## Controller Methods

### 1. `UserTierController::index()`

**Route:** `GET /admin/user-tiers`

**Mục đích:** Hiển thị trang danh sách khách hàng với tier

**Query Parameters:**

-   `search`: Tìm kiếm theo tên/email
-   `tier`: Lọc theo tier
-   `page`: Trang hiện tại

### 2. `UserTierController::getCustomersList()`

**Route:** `GET /admin/user-tiers/api/customers`

**Mục đích:** API endpoint trả về danh sách khách hàng dạng JSON

**Query Parameters:**

-   `search`: Tìm kiếm theo tên/email
-   `tier`: Lọc theo tier
-   `page`: Trang hiện tại
-   `per_page`: Số item mỗi trang

**Response:**

```json
{
    "success": true,
    "data": {
        "customers": [...],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 100,
            "last_page": 5,
            "from": 1,
            "to": 20
        }
    }
}
```

### 3. `UserTierController::getStatistics()`

**Route:** `GET /admin/user-tiers/api/statistics`

**Mục đích:** API endpoint trả về thống kê tier

**Response:**

```json
{
    "success": true,
    "data": {
        "total_customers": 100,
        "customers_with_tier": 80,
        "customers_without_tier": 20,
        "tier_distribution": {
            "Diamond": {
                "count": 10,
                "percentage": 10.0,
                "total_orders": 95000,
                "color": "#007bff"
            }
        },
        "total_orders": 150000,
        "average_orders_per_customer": 1875.0
    }
}
```

## Routes cần thêm

```php
// Trong routes/web.php hoặc routes/admin.php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/user-tiers', [UserTierController::class, 'index'])->name('admin.user-tiers.index');
    Route::get('/user-tiers/api/customers', [UserTierController::class, 'getCustomersList'])->name('admin.user-tiers.api.customers');
    Route::get('/user-tiers/api/statistics', [UserTierController::class, 'getStatistics'])->name('admin.user-tiers.api.statistics');
    Route::get('/user-tiers/{user}', [UserTierController::class, 'show'])->name('admin.user-tiers.show');
    Route::post('/user-tiers/calculate', [UserTierController::class, 'calculateTiers'])->name('admin.user-tiers.calculate');
    Route::post('/user-tiers/{user}/calculate', [UserTierController::class, 'calculateTierForUser'])->name('admin.user-tiers.calculate-for-user');
    Route::put('/user-tiers/{user}', [UserTierController::class, 'updateTier'])->name('admin.user-tiers.update');
});
```

## Lưu ý

1. Tất cả các hàm đều loại trừ admin users
2. Tier được tính dựa trên tháng hiện tại
3. Số đơn hàng tháng trước được lấy từ tháng trước tháng hiện tại
4. Các hàm đều có xử lý lỗi và logging
5. Dữ liệu được format theo chuẩn Việt Nam (dd/mm/yyyy)
