# API Thống Kê Đơn Hàng - Dashboard

## Tổng Quan

API thống kê đơn hàng cung cấp các endpoint để lấy dữ liệu thống kê cho dashboard, bao gồm:

-   Thống kê tổng quan
-   Thống kê theo trạng thái đơn hàng
-   Thống kê theo warehouse
-   Thống kê doanh thu theo thời gian
-   Top sản phẩm bán chạy
-   Thống kê theo brand

## Authentication

Tất cả các API đều yêu cầu authentication thông qua middleware `auth.api.token`.

## Endpoints

### 1. Thống Kê Tổng Quan Dashboard

**Endpoint:** `GET /api/statistics/dashboard`

**Parameters:**

-   `period` (optional): Khoảng thời gian thống kê
    -   `day`: 1 ngày
    -   `week`: 1 tuần
    -   `month`: 1 tháng (mặc định)
    -   `year`: 1 năm

**Response:**

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

### 2. Thống Kê Theo Trạng Thái

**Endpoint:** `GET /api/statistics/status`

**Parameters:**

-   `period` (optional): Khoảng thời gian thống kê

**Response:**

```json
{
    "success": true,
    "data": {
        "pending": 50,
        "processed": 80,
        "failed": 10,
        "cancelled": 5,
        "on hold": 5
    }
}
```

### 3. Thống Kê Theo Warehouse

**Endpoint:** `GET /api/statistics/warehouse`

**Parameters:**

-   `period` (optional): Khoảng thời gian thống kê

**Response:**

```json
{
    "success": true,
    "data": {
        "US": 80,
        "UK": 50,
        "VN": 20
    }
}
```

### 4. Thống Kê Doanh Thu Theo Thời Gian

**Endpoint:** `GET /api/statistics/revenue`

**Parameters:**

-   `period` (optional): Khoảng thời gian thống kê

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "date": "2024-01-01",
            "revenue": 1000.0,
            "orders": 10
        }
    ]
}
```

### 5. Top Sản Phẩm Bán Chạy

**Endpoint:** `GET /api/statistics/top-products`

**Parameters:**

-   `period` (optional): Khoảng thời gian thống kê
-   `limit` (optional): Số lượng sản phẩm trả về (mặc định: 10)

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "part_number": "PROD001",
            "title": "T-Shirt Basic",
            "total_quantity": 100,
            "total_revenue": 5000.0
        }
    ]
}
```

### 6. Thống Kê Theo Brand

**Endpoint:** `GET /api/statistics/brands`

**Parameters:**

-   `period` (optional): Khoảng thời gian thống kê

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "brand": "Brand A",
            "order_count": 50
        }
    ]
}
```

## Error Response

Khi có lỗi xảy ra, API sẽ trả về response với format:

```json
{
    "success": false,
    "message": "Mô tả lỗi"
}
```

## Ví Dụ Sử Dụng

### JavaScript/Fetch API

```javascript
// Lấy thống kê dashboard
const response = await fetch("/api/statistics/dashboard?period=month", {
    headers: {
        Authorization: "Bearer YOUR_API_TOKEN",
        "Content-Type": "application/json",
    },
});

const data = await response.json();
console.log(data);
```

### cURL

```bash
# Lấy thống kê dashboard
curl -X GET "http://your-domain.com/api/statistics/dashboard?period=month" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"

# Lấy top sản phẩm
curl -X GET "http://your-domain.com/api/statistics/top-products?limit=5" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"
```

## Lưu Ý

1. Tất cả các API đều yêu cầu authentication
2. Thời gian được tính theo múi giờ của server
3. Doanh thu được tính dựa trên `print_price * quantity` của từng item
4. Các thống kê được cache để tăng hiệu suất
5. Có thể filter theo khoảng thời gian tùy chỉnh bằng parameter `period`
