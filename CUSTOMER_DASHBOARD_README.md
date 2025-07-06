# Customer Dashboard

## Tổng quan

Dashboard cho Customer được thiết kế để cung cấp thông tin thống kê cá nhân về đơn hàng, chi tiêu và tier của từng khách hàng. Dashboard này giúp khách hàng theo dõi hoạt động mua hàng của mình một cách dễ dàng.

## Tính năng

### 1. Thống kê tổng quan

-   **Tổng đơn hàng**: Số lượng đơn hàng trong khoảng thời gian được chọn
-   **Tổng chi tiêu**: Tổng số tiền đã chi tiêu
-   **Tổng sản phẩm**: Số lượng sản phẩm đã mua
-   **Giá trị trung bình/đơn**: Giá trị trung bình của mỗi đơn hàng
-   **Số dư Wallet**: Số tiền hiện có trong ví

### 2. Thông tin Tier

-   Hiển thị tier hiện tại của khách hàng (Wood, Silver, Gold, Diamond)
-   Phần trăm chiết khấu được hưởng (0%, 5%, 10%, 15%)
-   Tổng doanh thu và số đơn hàng đã tạo
-   Tiến độ đến tier tiếp theo với progress bar
-   Ngưỡng đơn hàng để đạt tier: Wood (0), Silver (1500), Gold (4500), Diamond (9000)
-   Quyền lợi theo từng tier
-   Tháng có hiệu lực của tier hiện tại

### 3. Biểu đồ thống kê

-   **Biểu đồ chi tiêu 7 ngày**: Thống kê chi tiêu và số đơn hàng trong 7 ngày gần nhất
-   **Biểu đồ trạng thái đơn hàng**: Phân bố đơn hàng theo trạng thái (completed, pending, processing, etc.)

### 4. Bảng thông tin chi tiết

-   **Đơn hàng gần đây**: 5 đơn hàng mới nhất với thông tin chi tiết
-   **Sản phẩm mua nhiều nhất**: Top 5 sản phẩm được mua nhiều nhất

### 5. Bộ lọc thời gian

-   Hôm nay
-   Tuần này
-   Tháng này
-   Năm nay
-   **Tùy chọn**: Chọn khoảng thời gian bất kỳ với date picker

## Tier System

### Cấp độ Tier

-   **Wood (Level 1)**: 0 đơn hàng - Chiết khấu 0%
-   **Silver (Level 2)**: 1,500 đơn hàng - Chiết khấu 5% + Ưu tiên xử lý
-   **Gold (Level 3)**: 4,500 đơn hàng - Chiết khấu 10% + Ưu tiên xử lý + Hỗ trợ VIP
-   **Diamond (Level 4)**: 9,000 đơn hàng - Chiết khấu 15% + Tất cả quyền lợi + Miễn phí vận chuyển

### Cách tính Tier

-   Tier được tính dựa trên tổng số đơn hàng trong tháng (TẤT CẢ orders, không phân biệt status)
-   Revenue được tính từ TẤT CẢ orders (pending, processed, failed, cancelled)
-   Tier có hiệu lực từ tháng được tính toán
-   Sử dụng model `UserTier` để lưu trữ và quản lý

## Cấu trúc Code

### Controller

```php
app/Http/Controllers/CustomerDashboardController.php
```

### View

```php
resources/views/customer/dashboard.blade.php
```

### Routes

```php
// routes/web.php
Route::get('/', [CustomerDashboardController::class, 'index'])->name('customer.index');
Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
```

### CSS

```css
public/css/customer-dashboard.css
```

## Các phương thức chính trong Controller

### `index()`

-   Phương thức chính hiển thị dashboard
-   Lấy tham số `period` từ request (day, week, month, year, custom)
-   Xử lý custom date range với `start_date` và `end_date`
-   Tính toán các thống kê theo period hoặc date range

### `calculateTotalSpending()`

-   Tính tổng chi tiêu của customer trong khoảng thời gian từ TẤT CẢ orders (mọi status)
-   Hỗ trợ custom date range với `endDate` parameter

### `calculateTotalItems()`

-   Tính tổng số sản phẩm đã mua
-   Hỗ trợ custom date range với `endDate` parameter

### `getOrderStatusStats()`

-   Lấy thống kê đơn hàng theo trạng thái
-   Hỗ trợ custom date range với `endDate` parameter

### `getRecentOrders()`

-   Lấy danh sách đơn hàng gần đây
-   Không phụ thuộc vào period filter (luôn lấy mới nhất)

### `getDailySpending()`

-   Lấy thống kê chi tiêu hàng ngày
-   Luôn lấy 7 ngày gần nhất cho biểu đồ line chart

### `getTopProducts()`

-   Lấy top sản phẩm được mua nhiều nhất
-   Hỗ trợ custom date range với `endDate` parameter

### `getTierInfo()`

-   Lấy thông tin tier của customer
-   Sử dụng `UserTier::getCurrentTier()` để lấy tier hiện tại
-   Tính toán progress đến tier tiếp theo
-   Định nghĩa discount percentages và tier levels
-   Trả về thông tin chi tiết bao gồm tier thresholds và progress

## Cách sử dụng

### Truy cập Dashboard

```
/customer/dashboard
```

### Thay đổi khoảng thời gian

Sử dụng dropdown "Khoảng thời gian" để chọn:

-   Hôm nay
-   Tuần này
-   Tháng này
-   Năm nay
-   **Tùy chọn**: Chọn ngày bắt đầu và ngày kết thúc tùy ý

#### Sử dụng Custom Date Range

1. Chọn "Tùy chọn" trong dropdown
2. Date picker sẽ xuất hiện với 2 trường: "Từ ngày" và "Đến ngày"
3. Chọn ngày bắt đầu và ngày kết thúc
4. Click "Áp dụng" để xem thống kê
5. Header sẽ hiển thị khoảng thời gian đã chọn

**Lưu ý**:

-   Ngày bắt đầu không được lớn hơn ngày kết thúc
-   Nếu không chọn ngày, mặc định sẽ là 30 ngày gần nhất

### Xem chi tiết đơn hàng

Click vào đơn hàng trong bảng "Đơn hàng gần đây" để xem chi tiết.

## Customization

### Thay đổi số lượng hiển thị

Trong `CustomerDashboardController.php`:

```php
// Thay đổi số đơn hàng gần đây hiển thị
$recentOrders = $this->getRecentOrders($user->id, 10); // Từ 5 thành 10

// Thay đổi số sản phẩm top hiển thị
$topProducts = $this->getTopProducts($user->id, $startDate, 10); // Từ 5 thành 10
```

### Thêm thống kê mới

Tạo phương thức mới trong controller:

```php
private function getCustomStat($userId, $startDate)
{
    // Logic tính toán thống kê
    return $result;
}
```

Thêm vào method `index()`:

```php
$customStat = $this->getCustomStat($user->id, $startDate);
```

Truyền vào view:

```php
return view('customer.dashboard', compact(
    // ... existing variables
    'customStat'
));
```

### Thay đổi giao diện

Chỉnh sửa file CSS:

```css
/* public/css/customer-dashboard.css */
.card-hover {
    /* Thay đổi hiệu ứng hover */
}
```

## Lưu ý

1. **Middleware**: Dashboard yêu cầu middleware `auth` để đảm bảo chỉ user đã đăng nhập mới truy cập được

2. **Permissions**: Dashboard hiển thị dữ liệu của user hiện tại, không có quyền xem dữ liệu user khác

3. **Performance**: Các query được tối ưu hóa nhưng có thể cần cache cho website có lượng truy cập lớn

4. **Responsive**: Dashboard được thiết kế responsive, hoạt động tốt trên mobile và desktop

5. **Revenue Logic**: Hệ thống đã được cập nhật để tính revenue từ TẤT CẢ orders (bao gồm pending, processed, failed, cancelled) thay vì chỉ tính orders đã processed như trước đây. Điều này đảm bảo tính nhất quán giữa Dashboard và Tier System.

## Troubleshooting

### Lỗi không hiển thị dữ liệu

-   Kiểm tra kết nối database
-   Đảm bảo user có đơn hàng trong hệ thống
-   Kiểm tra migration cho bảng `excel_orders` và `excel_order_items`

### Lỗi biểu đồ không hiển thị

-   Kiểm tra Chart.js đã được load
-   Kiểm tra dữ liệu `$chartData` có đúng format
-   Kiểm tra console browser để xem lỗi JavaScript

### Lỗi CSS không áp dụng

-   Kiểm tra file CSS đã được tạo và có đúng đường dẫn
-   Clear browser cache
-   Kiểm tra section `@section('styles')` trong view
