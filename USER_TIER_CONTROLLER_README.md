# 🏆 UserTierController - Quản lý Tier Khách hàng

## 📋 Mô tả

`UserTierController` cung cấp giao diện quản lý tier cho khách hàng với đầy đủ thông tin theo yêu cầu:

-   Tên khách hàng
-   Email
-   Số điện thoại
-   Tier hiện tại
-   Số đơn hàng tháng trước
-   Tháng áp dụng tier
-   Ngày cập nhật tier
-   Hành động

## 🚀 Tính năng

### 1. Danh sách khách hàng kèm tier (`index`)

-   **URL**: `/admin/user-tiers`
-   **Method**: GET
-   **Chức năng**: Hiển thị danh sách tất cả khách hàng với thông tin tier chi tiết

#### Thông tin hiển thị:

-   ✅ Tên khách hàng
-   ✅ Email
-   ✅ Số điện thoại
-   ✅ Tier hiện tại (với icon và màu sắc)
-   ✅ Số đơn hàng tháng trước
-   ✅ Tháng áp dụng tier
-   ✅ Ngày cập nhật tier
-   ✅ Hành động (Xem, Tính toán, Chỉnh sửa)

#### Bộ lọc:

-   **Tìm kiếm**: Theo tên, email, số điện thoại
-   **Lọc theo tier**: Diamond, Gold, Silver, Wood
-   **Phân trang**: 20 khách hàng/trang

#### Thống kê tổng quan:

-   Tổng số khách hàng
-   Phân bố theo tier (Diamond, Gold, Silver, Wood)

### 2. Chi tiết tier của khách hàng (`show`)

-   **URL**: `/admin/user-tiers/{user}`
-   **Method**: GET
-   **Chức năng**: Hiển thị chi tiết tier của một khách hàng cụ thể

#### Thông tin hiển thị:

-   Thông tin khách hàng (tên, email, SĐT, ngày tham gia)
-   Tier hiện tại với icon lớn
-   Thống kê đơn hàng (tháng này, tháng trước)
-   Hướng dẫn lên tier tiếp theo
-   Lịch sử tier theo tháng

### 3. Tính toán tier cho tất cả khách hàng (`calculateTiers`)

-   **URL**: `/admin/user-tiers/calculate`
-   **Method**: POST
-   **Chức năng**: Tính toán và cập nhật tier cho tất cả khách hàng

### 4. Tính toán tier cho khách hàng cụ thể (`calculateTierForUser`)

-   **URL**: `/admin/user-tiers/{user}/calculate`
-   **Method**: POST
-   **Chức năng**: Tính toán tier cho một khách hàng cụ thể

### 5. Thống kê tier (`getStatistics`)

-   **URL**: `/admin/user-tiers/statistics`
-   **Method**: GET
-   **Chức năng**: Lấy thống kê phân bố tier

### 6. Cập nhật tier thủ công (`updateTier`)

-   **URL**: `/admin/user-tiers/{user}`
-   **Method**: PUT
-   **Chức năng**: Cập nhật tier thủ công cho khách hàng

## 🎨 Giao diện

### Trang danh sách (`/admin/user-tiers`)

```
┌─────────────────────────────────────────────────────────────────┐
│                    Danh sách Tier Khách hàng                    │
│ [Tính toán Tier]                                               │
├─────────────────────────────────────────────────────────────────┤
│ 📊 Thống kê tổng quan                                          │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐               │
│ │ Tổng KH │ │Diamond  │ │  Gold   │ │ Silver  │               │
│ │   150   │ │   5     │ │   25    │ │   80    │               │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘               │
├─────────────────────────────────────────────────────────────────┤
│ 🔍 Bộ lọc                                                       │
│ [Tìm kiếm...] [Tất cả Tier ▼] [Xóa bộ lọc]                    │
├─────────────────────────────────────────────────────────────────┤
│ STT │ Tên KH │ Email │ SĐT │ Tier │ Đơn hàng │ Tháng │ Cập nhật │ HĐ │
│  1  │ John   │ ...   │ ... │ 💎   │   5000   │ 2024-2│ 01/02   │ 👁 │
│     │ Doe    │       │     │Diamond│   đơn    │       │ 10:30   │ ⚡ │
│     │        │       │     │       │          │       │         │ ✏️ │
└─────────────────────────────────────────────────────────────────┘
```

### Trang chi tiết (`/admin/user-tiers/{user}`)

```
┌─────────────────────────────────────────────────────────────────┐
│                    Chi tiết Tier - John Doe                     │
│ [Quay lại]                                                      │
├─────────────────────────────────────────────────────────────────┤
│ 👤 Thông tin khách hàng    │ 🏆 Tier hiện tại                   │
│ Tên: John Doe              │         💎                         │
│ Email: john@example.com    │      Diamond                       │
│ SĐT: +1234567890           │                                    │
│ Ngày tham gia: 01/01/2024  │ Số đơn hàng hiện tại: 5000 đơn     │
│                            │ Số đơn hàng tháng này: 1200 đơn    │
│                            │ Tier dự kiến: Gold                 │
├─────────────────────────────────────────────────────────────────┤
│ 📊 Thông tin Tier chi tiết                                      │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐               │
│ │Tháng này│ │Tháng trước│ │Hiệu lực │ │Dự kiến │               │
│ │  1200   │ │  5000    │ │ 2024-2  │ │  Gold  │               │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘               │
├─────────────────────────────────────────────────────────────────┤
│ 💡 Hướng dẫn lên tier                                            │
│ Để lên tier Diamond, khách hàng cần thêm 7800 đơn hàng nữa     │
│ (tổng cộng 9000 đơn).                                           │
├─────────────────────────────────────────────────────────────────┤
│ 📅 Lịch sử Tier                                                 │
│ Tháng │ Tier │ Số đơn hàng │ Ngày cập nhật                      │
│ 2024-2│ 💎   │   5000 đơn  │ 01/02/2024 10:30                   │
│ 2024-1│ 🥇   │   4500 đơn  │ 01/01/2024 10:30                   │
└─────────────────────────────────────────────────────────────────┘
```

## 🔧 Cài đặt

### 1. Routes

Routes đã được thêm vào `routes/web.php`:

```php
// User Tier routes
Route::get('/user-tiers', [UserTierController::class, 'index'])->name('admin.user-tiers.index');
Route::get('/user-tiers/{user}', [UserTierController::class, 'show'])->name('admin.user-tiers.show');
Route::post('/user-tiers/calculate', [UserTierController::class, 'calculateTiers'])->name('admin.user-tiers.calculate');
Route::post('/user-tiers/{user}/calculate', [UserTierController::class, 'calculateTierForUser'])->name('admin.user-tiers.calculate-user');
Route::get('/user-tiers/statistics', [UserTierController::class, 'getStatistics'])->name('admin.user-tiers.statistics');
Route::put('/user-tiers/{user}', [UserTierController::class, 'updateTier'])->name('admin.user-tiers.update');
```

### 2. Views

-   `resources/views/admin/user-tiers/index.blade.php` - Trang danh sách
-   `resources/views/admin/user-tiers/show.blade.php` - Trang chi tiết

### 3. Controller

-   `app/Http/Controllers/Admin/UserTierController.php` - Controller chính

## 📊 Cấu trúc dữ liệu

### Thông tin tier cho mỗi user:

```php
$user->tier_info = [
    'current_tier' => 'Diamond',           // Tier hiện tại
    'current_order_count' => 5000,         // Số đơn hàng tháng trước
    'last_month_order_count' => 5000,      // Số đơn hàng tháng trước
    'last_month_tier' => 'Gold',           // Tier tháng trước
    'effective_month' => '2024-02',        // Tháng áp dụng tier
    'updated_at' => '2024-02-01 10:30:00', // Ngày cập nhật tier
    'tier_change' => 'changed'             // Trạng thái thay đổi
];
```

### Thống kê tier:

```php
$tierStats = [
    'total_users' => 150,
    'tier_distribution' => [
        'Diamond' => ['user_count' => 5, 'percentage' => 3.33, 'avg_orders' => 12000],
        'Gold' => ['user_count' => 25, 'percentage' => 16.67, 'avg_orders' => 6000],
        'Silver' => ['user_count' => 80, 'percentage' => 53.33, 'avg_orders' => 2500],
        'Wood' => ['user_count' => 40, 'percentage' => 26.67, 'avg_orders' => 800]
    ],
    'effective_month' => '2024-02'
];
```

## 🎯 Sử dụng

### 1. Truy cập trang danh sách

```
GET /admin/user-tiers
```

### 2. Tìm kiếm khách hàng

```
GET /admin/user-tiers?search=john
```

### 3. Lọc theo tier

```
GET /admin/user-tiers?tier=Gold
```

### 4. Xem chi tiết khách hàng

```
GET /admin/user-tiers/123
```

### 5. Tính toán tier cho tất cả

```javascript
$.ajax({
    url: "/admin/user-tiers/calculate",
    method: "POST",
    data: { _token: "{{ csrf_token() }}" },
    success: function (response) {
        if (response.success) {
            alert("Tính toán tier thành công!");
            location.reload();
        }
    },
});
```

### 6. Cập nhật tier thủ công

```javascript
const formData = new FormData();
formData.append("tier", "Gold");
formData.append("order_count", "5000");
formData.append("effective_month", "2024-02-01");

$.ajax({
    url: "/admin/user-tiers/123",
    method: "PUT",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
        if (response.success) {
            alert("Cập nhật tier thành công!");
        }
    },
});
```

## 🧪 Testing

Chạy file test để kiểm tra chức năng:

```bash
php test_user_tier_controller.php
```

## 🔍 Tính năng nâng cao

### 1. Badge và Icon cho tier

-   **Diamond**: 💎 `fas fa-gem` (màu đỏ)
-   **Gold**: 🥇 `fas fa-medal` (màu vàng)
-   **Silver**: 🥈 `fas fa-award` (màu xanh)
-   **Wood**: 🌳 `fas fa-tree` (màu xám)

### 2. Trạng thái thay đổi tier

-   **Thay đổi**: Badge màu vàng "Thay đổi"
-   **Mới**: Badge màu xanh "Mới"
-   **Giữ nguyên**: Không có badge

### 3. Hướng dẫn lên tier

Hiển thị thông tin cần thiết để lên tier tiếp theo:

-   Tier mục tiêu
-   Số đơn hàng cần thêm
-   Tổng số đơn hàng cần thiết

### 4. Lịch sử tier

Hiển thị lịch sử tier theo tháng với:

-   Tháng áp dụng
-   Tier
-   Số đơn hàng
-   Ngày cập nhật

## ⚠️ Lưu ý

1. **Performance**: Với hệ thống lớn, nên sử dụng pagination và caching
2. **Security**: Tất cả routes đều có middleware `auth` và `AdminMiddleware`
3. **Validation**: Có validation cho việc cập nhật tier thủ công
4. **Logging**: Tất cả thao tác đều được log để theo dõi
5. **Error Handling**: Có xử lý lỗi đầy đủ với thông báo rõ ràng

## 🔄 Cập nhật

### Version 1.0

-   ✅ Danh sách khách hàng với đầy đủ thông tin tier
-   ✅ Tìm kiếm và lọc theo tier
-   ✅ Chi tiết tier của khách hàng
-   ✅ Tính toán tier tự động
-   ✅ Cập nhật tier thủ công
-   ✅ Thống kê phân bố tier
-   ✅ Giao diện responsive và thân thiện
