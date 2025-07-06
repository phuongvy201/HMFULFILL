# Hướng dẫn sử dụng Giao diện Quản lý Tier Khách hàng

## Tổng quan

Giao diện quản lý tier khách hàng đã được cập nhật với thiết kế Tailwind CSS hiện đại, cung cấp trải nghiệm người dùng tốt hơn và hiển thị thông tin chi tiết về tier của khách hàng.

## Tính năng chính

### 1. Thống kê tổng quan

-   **Tổng khách hàng**: Hiển thị tổng số khách hàng trong hệ thống
-   **Diamond Tier**: Số khách hàng ở tier cao nhất với phần trăm
-   **Gold Tier**: Số khách hàng ở tier vàng với phần trăm
-   **Silver Tier**: Số khách hàng ở tier bạc với phần trăm

### 2. Bộ lọc và tìm kiếm

-   **Tìm kiếm**: Tìm kiếm theo tên, email, số điện thoại
-   **Lọc theo tier**: Lọc khách hàng theo tier cụ thể (Diamond, Gold, Silver, Wood)
-   **Xóa bộ lọc**: Nút để xóa tất cả bộ lọc hiện tại

### 3. Danh sách khách hàng

Hiển thị thông tin chi tiết cho mỗi khách hàng:

-   **STT**: Số thứ tự
-   **Tên khách hàng**: Tên đầy đủ với badge thay đổi/mới
-   **Email**: Địa chỉ email
-   **Số điện thoại**: Số điện thoại (hoặc N/A)
-   **Tier hiện tại**: Tier hiện tại với icon và số đơn hàng
-   **Số đơn hàng tháng trước**: Số đơn hàng tháng trước với tier cũ (nếu có thay đổi)
-   **Tháng áp dụng tier**: Tháng tier có hiệu lực
-   **Ngày cập nhật tier**: Thời gian cập nhật tier gần nhất
-   **Hành động**: Các nút xem, tính toán, chỉnh sửa

### 4. Hành động

-   **Xem**: Xem chi tiết tier của khách hàng
-   **Tính toán**: Tính toán lại tier cho khách hàng cụ thể
-   **Chỉnh sửa**: Chỉnh sửa tier thủ công

## Cách sử dụng

### Truy cập giao diện

```
/admin/user-tiers
```

### Tính toán tier cho tất cả khách hàng

1. Nhấn nút "Tính toán Tier" ở đầu trang
2. Xác nhận trong hộp thoại
3. Hệ thống sẽ hiển thị loading và thông báo kết quả

### Tìm kiếm khách hàng

1. Nhập từ khóa vào ô tìm kiếm (tên, email, SĐT)
2. Nhấn nút "Tìm kiếm" hoặc Enter

### Lọc theo tier

1. Chọn tier từ dropdown "Tất cả Tier"
2. Hệ thống sẽ tự động lọc và hiển thị kết quả

### Xem chi tiết tier

1. Nhấn nút "Xem" trong cột Hành động
2. Hệ thống sẽ chuyển đến trang chi tiết tier

## Cấu trúc dữ liệu

### UserTier Model

```php
protected $fillable = [
    'user_id',
    'tier',
    'order_count',
    'effective_month'
];
```

### Tier Thresholds

-   **Diamond**: ≥ 9,000 đơn hàng
-   **Gold**: ≥ 4,500 đơn hàng
-   **Silver**: ≥ 1,500 đơn hàng
-   **Wood**: < 1,500 đơn hàng

## API Endpoints

### Tính toán tier cho tất cả

```
POST /admin/user-tiers/calculate
```

### Tính toán tier cho user cụ thể

```
POST /admin/user-tiers/{user}/calculate
```

### Lấy thống kê

```
GET /admin/user-tiers/statistics
```

### Cập nhật tier thủ công

```
PUT /admin/user-tiers/{user}
```

## Giao diện

### Thiết kế

-   Sử dụng Tailwind CSS với dark mode support
-   Responsive design cho mobile và desktop
-   Icons từ Font Awesome
-   Animations và transitions mượt mà

### Màu sắc tier

-   **Diamond**: Đỏ (red-600)
-   **Gold**: Vàng (yellow-600)
-   **Silver**: Xanh dương (blue-600)
-   **Wood**: Xám (gray-600)

### Notifications

-   Thông báo thành công: Màu xanh lá
-   Thông báo lỗi: Màu đỏ
-   Tự động ẩn sau 5 giây
-   Có thể đóng thủ công

## Testing

Chạy file test để kiểm tra chức năng:

```bash
php test_user_tier_interface.php
```

## Troubleshooting

### Lỗi thường gặp

1. **Không hiển thị dữ liệu**: Kiểm tra database connection và migration
2. **Tính toán tier lỗi**: Kiểm tra ExcelOrder table và relationship
3. **Giao diện không load**: Kiểm tra Tailwind CSS và JavaScript

### Debug

-   Kiểm tra logs trong `storage/logs/laravel.log`
-   Sử dụng `dd()` để debug dữ liệu
-   Kiểm tra network tab trong browser developer tools

## Cập nhật gần đây

### v2.0.0 (Hiện tại)

-   ✅ Cập nhật giao diện sang Tailwind CSS
-   ✅ Thêm thống kê tổng quan với metrics cards
-   ✅ Cải thiện UX với notifications và loading states
-   ✅ Responsive design cho mobile
-   ✅ Dark mode support
-   ✅ Sửa lỗi effective_month vs month

### v1.0.0 (Trước đó)

-   Giao diện Bootstrap cơ bản
-   Chức năng tính toán tier
-   Danh sách khách hàng đơn giản
