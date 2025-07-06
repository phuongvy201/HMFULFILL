# 🏆 Hệ Thống Tier User

## 📋 Mô tả

Hệ thống tier phân loại user dựa trên số đơn hàng trong tháng, áp dụng giá theo tier từ tháng tiếp theo.

## 🎯 Bảng Tier

| Tier        | Số đơn hàng / tháng | Mô tả           |
| ----------- | ------------------- | --------------- |
| **Diamond** | ≥ 9,000 đơn         | Tier cao nhất   |
| **Gold**    | ≥ 4,500 đơn         | Tier cao        |
| **Silver**  | ≥ 1,500 đơn         | Tier trung bình |
| **Wood**    | < 1,500 đơn         | Tier cơ bản     |

## ⏱ Cách hoạt động

1. **Thời điểm cập nhật**: Ngày đầu tiên của mỗi tháng lúc 2:00 AM
2. **Dữ liệu tính toán**: Đếm tổng số đơn hàng của từng user trong tháng hiện tại
3. **Áp dụng tier**: Tier mới có hiệu lực ngay lập tức cho tháng hiện tại
4. **Giá áp dụng**: Các đơn hàng mới trong tháng sẽ được áp dụng giá theo tier vừa tính

## 🚀 Cài đặt

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Đăng ký Commands (nếu cần)

Các commands đã được tự động load trong `app/Console/Kernel.php`

### 3. Cài đặt Cron Job

Thêm vào crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Sử dụng

### Tính toán tier thủ công

#### Cho tất cả user:

```bash
# Tính toán cho tháng trước (mặc định)
php artisan users:calculate-tiers

# Tính toán cho tháng cụ thể (ví dụ: tháng 6)
php artisan users:calculate-tiers --month=2024-06

# Tính toán cho tháng 5
php artisan users:calculate-tiers --month=2024-05
```

#### Cho user cụ thể:

```bash
# Tính toán cho user ID 123 (tháng trước)
php artisan users:calculate-tiers --user-id=123

# Tính toán cho user ID 123 với tháng cụ thể
php artisan users:calculate-tiers --user-id=123 --month=2024-06
```

### Schedule job tự động:

```bash
php artisan users:schedule-tier-calculation
```

## 🔧 API Endpoints

### Admin Panel

#### 1. Danh sách tier của user

```
GET /admin/user-tiers
```

**Query Parameters:**

-   `tier`: Lọc theo tier (Diamond, Gold, Silver, Wood)
-   `search`: Tìm kiếm theo tên hoặc email

#### 2. Chi tiết tier của user

```
GET /admin/user-tiers/{user}
```

#### 3. Tính toán tier cho tất cả user

```
POST /admin/user-tiers/calculate
```

**Body:**

```json
{
    "month": "2024-01" // Optional, format: YYYY-MM
}
```

#### 4. Tính toán tier cho user cụ thể

```
POST /admin/user-tiers/{user}/calculate
```

#### 5. Thống kê tier

```
GET /admin/user-tiers/statistics
```

#### 6. Cập nhật tier thủ công

```
PUT /admin/user-tiers/{user}
```

**Body:**

```json
{
    "tier": "Gold",
    "order_count": 5000,
    "effective_month": "2024-02-01"
}
```

## 💻 Sử dụng trong Code

### Lấy tier hiện tại của user

```php
use App\Services\UserTierService;

$tierService = app(UserTierService::class);
$currentTier = $tierService->getCurrentTier($userId);
```

### Lấy thông tin tier chi tiết

```php
$tierInfo = $tierService->getUserTierInfo($userId);

// Kết quả:
[
    'current_tier' => 'Gold',
    'current_order_count' => 5000,
    'this_month_orders' => 1200,
    'projected_tier' => 'Silver',
    'next_tier_threshold' => [
        'tier' => 'Diamond',
        'threshold' => 9000,
        'orders_needed' => 7800
    ],
    'effective_month' => '2024-02'
]
```

### Lấy thống kê tier

```php
$stats = $tierService->getTierStatistics();

// Kết quả:
[
    'total_users' => 150,
    'tier_distribution' => [
        'Diamond' => ['user_count' => 5, 'percentage' => 3.33, 'avg_orders' => 12000],
        'Gold' => ['user_count' => 25, 'percentage' => 16.67, 'avg_orders' => 6000],
        'Silver' => ['user_count' => 80, 'percentage' => 53.33, 'avg_orders' => 2500],
        'Wood' => ['user_count' => 40, 'percentage' => 26.67, 'avg_orders' => 800]
    ],
    'effective_month' => '2024-02'
]
```

## 📁 Cấu trúc Files

```
app/
├── Console/Commands/
│   ├── CalculateUserTiers.php          # Command tính toán tier
│   └── ScheduleTierCalculation.php     # Command schedule job
├── Http/Controllers/Admin/
│   └── UserTierController.php          # Controller quản lý tier
├── Jobs/
│   └── CalculateUserTiersJob.php       # Job tính toán tier
├── Models/
│   ├── User.php                        # Model User (đã cập nhật)
│   ├── UserTier.php                    # Model UserTier
│   └── ExcelOrder.php                  # Model ExcelOrder (sử dụng created_by)
└── Services/
    └── UserTierService.php             # Service xử lý logic tier

database/migrations/
└── 2024_01_15_000000_create_user_tiers_table.php
```

## 🔍 Database Schema

### Bảng `user_tiers`

-   `id`: Primary key
-   `user_id`: Foreign key tới bảng `users`
-   `tier`: Enum (Diamond, Gold, Silver, Wood)
-   `order_count`: Số đơn hàng trong tháng
-   `effective_month`: Tháng có hiệu lực (YYYY-MM-01)
-   `created_at`, `updated_at`: Timestamps

### Bảng `excel_orders`

-   `created_by`: Foreign key tới bảng `users` (thay vì user_id)
-   Các trường khác...

## 🔍 Monitoring

### Logs

Hệ thống sẽ log các hoạt động quan trọng:

-   Bắt đầu/kết thúc tính toán tier
-   Chi tiết cập nhật tier cho từng user
-   Lỗi xảy ra trong quá trình tính toán

### Queue Jobs

Job tính toán tier sẽ được xử lý trong queue để tránh timeout:

-   Job name: `CalculateUserTiersJob`
-   Retry: 3 lần (mặc định)
-   Timeout: 300 giây

## ⚠️ Lưu ý

1. **Performance**: Với hệ thống lớn, nên chạy job trong queue
2. **Data Integrity**: Mỗi user chỉ có 1 tier cho mỗi tháng
3. **Backup**: Nên backup dữ liệu trước khi chạy tính toán tier
4. **Testing**: Test kỹ trước khi deploy lên production
5. **Field Mapping**: Hệ thống sử dụng `created_by` trong bảng `excel_orders` để liên kết với user

## 🐛 Troubleshooting

### Lỗi thường gặp

1. **Job không chạy**: Kiểm tra cron job và queue worker
2. **Tier không cập nhật**: Kiểm tra log và dữ liệu đơn hàng
3. **Performance chậm**: Tối ưu query hoặc chạy trong queue

### Debug Commands

```bash
# Kiểm tra queue
php artisan queue:work

# Xem log
tail -f storage/logs/laravel.log

# Test tính toán tier
php artisan users:calculate-tiers --user-id=1
```

### Ví dụ thực tế:

```bash
# Đang ở tháng 7, muốn tính tier cho tháng 6
php artisan users:calculate-tiers --month=2024-06

# Đang ở tháng 7, muốn tính tier cho tháng 5
php artisan users:calculate-tiers --month=2024-05

# Tính tier cho user cụ thể trong tháng 6
php artisan users:calculate-tiers --user-id=123 --month=2024-06
```
