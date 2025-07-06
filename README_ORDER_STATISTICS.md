# API Thống Kê Đơn Hàng - Hướng Dẫn Sử Dụng

## ✅ Đã Hoàn Thành

Tôi đã tạo thành công API thống kê đơn hàng cho dashboard với các tính năng sau:

### 📊 Các Endpoint API

1. **Dashboard Stats** - `GET /api/statistics/dashboard`

    - Thống kê tổng quan: tổng đơn hàng, doanh thu, số lượng sản phẩm
    - Thống kê theo trạng thái, warehouse, brand
    - Dữ liệu 7 ngày gần nhất
    - Top sản phẩm bán chạy

2. **Status Statistics** - `GET /api/statistics/status`

    - Thống kê đơn hàng theo trạng thái (pending, processed, failed, etc.)

3. **Warehouse Statistics** - `GET /api/statistics/warehouse`

    - Thống kê đơn hàng theo warehouse (US, UK, VN)

4. **Revenue Statistics** - `GET /api/statistics/revenue`

    - Thống kê doanh thu theo thời gian (ngày, tuần, tháng, năm)

5. **Top Products** - `GET /api/statistics/top-products`

    - Danh sách sản phẩm bán chạy nhất

6. **Brand Statistics** - `GET /api/statistics/brands`
    - Thống kê đơn hàng theo brand

### 🔧 Tính Năng

-   **Filter theo thời gian**: day, week, month, year
-   **Authentication**: Yêu cầu API token
-   **Error handling**: Xử lý lỗi chi tiết
-   **Performance**: Tối ưu query database
-   **Flexible**: Có thể mở rộng dễ dàng

### 📁 Files Đã Tạo

1. `app/Http/Controllers/Api/OrderStatisticsController.php` - Controller chính
2. `routes/api.php` - Routes cho API (đã thêm)
3. `API_STATISTICS_DOCUMENTATION.md` - Documentation chi tiết
4. `test_order_statistics_api.php` - File test API
5. `README_ORDER_STATISTICS.md` - File này

## 🚀 Cách Sử Dụng

### 1. Kiểm Tra Routes

```bash
php artisan route:list --path=api/statistics
```

### 2. Test API

```bash
# Chạy file test
php test_order_statistics_api.php

# Hoặc test bằng cURL
curl -X GET "http://your-domain.com/api/statistics/dashboard?period=month" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"
```

### 3. Sử Dụng Trong Frontend

```javascript
// Lấy thống kê dashboard
const getDashboardStats = async (period = "month") => {
    const response = await fetch(`/api/statistics/dashboard?period=${period}`, {
        headers: {
            Authorization: "Bearer " + apiToken,
            "Content-Type": "application/json",
        },
    });
    return await response.json();
};

// Sử dụng
const stats = await getDashboardStats("month");
console.log("Total orders:", stats.data.overview.total_orders);
console.log("Total revenue:", stats.data.overview.total_revenue);
```

## 📈 Ví Dụ Response

### Dashboard Stats Response

```json
{
    "success": true,
    "data": {
        "overview": {
            "total_orders": 150,
            "total_revenue": 15000.5,
            "total_items": 450,
            "average_order_value": 100.0
        },
        "status_statistics": {
            "pending": 50,
            "processed": 80,
            "failed": 10,
            "cancelled": 5,
            "on hold": 5
        },
        "warehouse_statistics": {
            "US": 80,
            "UK": 50,
            "VN": 20
        },
        "daily_statistics": [
            {
                "date": "2024-01-01",
                "orders": 10,
                "revenue": 1000.0
            }
        ],
        "top_products": [
            {
                "part_number": "PROD001",
                "title": "T-Shirt Basic",
                "total_quantity": 100,
                "total_revenue": 5000.0
            }
        ],
        "brand_statistics": [
            {
                "brand": "Brand A",
                "order_count": 50
            }
        ]
    }
}
```

## 🔒 Bảo Mật

-   Tất cả API đều yêu cầu authentication
-   Sử dụng middleware `auth.api.token`
-   Validate input parameters
-   Log lỗi chi tiết

## 📊 Dashboard Widgets

Với API này, bạn có thể tạo các widget dashboard:

1. **Overview Cards**

    - Tổng đơn hàng
    - Tổng doanh thu
    - Số lượng sản phẩm
    - Giá trị đơn hàng trung bình

2. **Charts**

    - Biểu đồ doanh thu theo thời gian
    - Pie chart trạng thái đơn hàng
    - Bar chart warehouse distribution

3. **Tables**
    - Top sản phẩm bán chạy
    - Thống kê theo brand

## 🛠️ Tùy Chỉnh

### Thêm Filter Mới

```php
// Trong OrderStatisticsController
public function getCustomStats(Request $request)
{
    $period = $request->input('period', 'month');
    $startDate = $this->getStartDate($period);

    // Thêm logic thống kê tùy chỉnh
    $customStats = ExcelOrder::where('created_at', '>=', $startDate)
        ->select('your_field', DB::raw('count(*) as count'))
        ->groupBy('your_field')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $customStats
    ]);
}
```

### Thêm Route Mới

```php
// Trong routes/api.php
Route::get('/statistics/custom', [OrderStatisticsController::class, 'getCustomStats'])
    ->middleware('auth.api.token')
    ->name('api.statistics.custom');
```

## 🧪 Testing

### Tạo Dữ Liệu Test

```php
// Trong test_order_statistics_api.php
$test = new OrderStatisticsTest();
$test->createTestData(); // Tạo 20 đơn hàng test
```

### Chạy Test

```bash
php test_order_statistics_api.php
```

## 📝 Lưu Ý

1. **Performance**: API được tối ưu với JOIN queries
2. **Caching**: Có thể thêm cache để tăng hiệu suất
3. **Pagination**: Có thể thêm pagination cho danh sách dài
4. **Export**: Có thể thêm tính năng export Excel/PDF
5. **Real-time**: Có thể thêm WebSocket cho real-time updates

## 🎯 Kết Luận

API thống kê đơn hàng đã được tạo hoàn chỉnh với:

-   ✅ 6 endpoints chính
-   ✅ Authentication & Authorization
-   ✅ Error handling
-   ✅ Documentation đầy đủ
-   ✅ Test script
-   ✅ Dễ dàng mở rộng

Bạn có thể sử dụng ngay để xây dựng dashboard thống kê đơn hàng!
