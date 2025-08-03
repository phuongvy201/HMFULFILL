# USPS Tracking Service Guide

## Tổng quan

Service này cho phép bạn theo dõi thông tin gói hàng thông qua USPS API. Dựa trên tài liệu USPS Track/Confirm Web Tools API, service hỗ trợ:

-   Track một tracking number
-   Track nhiều tracking numbers (tối đa 35)
-   Kiểm tra trạng thái giao hàng
-   Cache kết quả để tối ưu hiệu suất

## Cài đặt

### 1. Cấu hình Environment Variables

Thêm các biến môi trường vào file `.env`:

```env
USPS_USER_ID=your_usps_user_id
USPS_PASSWORD=your_usps_password
```

### 2. Đăng ký USPS Web Tools

Để sử dụng USPS API, bạn cần:

1. Truy cập [USPS Web Tools](https://www.usps.com/business/web-tools-apis/)
2. Đăng ký tài khoản để lấy USERID
3. Kích hoạt Track/Confirm API

## Sử dụng

### 1. Command Line

#### Test tracking service:

```bash
php artisan usps:test-tracking TRACKING_NUMBER
```

Ví dụ:

```bash
php artisan usps:test-tracking 9400100000000000000000
```

### 2. Web Interface

Truy cập: `/customer/tracking`

-   **Track Single Package**: Nhập một tracking number
-   **Track Multiple Packages**: Nhập nhiều tracking numbers (mỗi dòng một số)

### 3. API Endpoints

#### Track Single Package

```http
POST /customer/tracking/single
Content-Type: application/json

{
    "tracking_number": "9400100000000000000000"
}
```

#### Track Multiple Packages

```http
POST /customer/tracking/multiple
Content-Type: application/json

{
    "tracking_numbers": [
        "9400100000000000000000",
        "9400100000000000000001"
    ]
}
```

#### Check Delivery Status

```http
POST /customer/tracking/status
Content-Type: application/json

{
    "tracking_number": "9400100000000000000000"
}
```

#### Track with Cache

```http
POST /customer/tracking/cache
Content-Type: application/json

{
    "tracking_number": "9400100000000000000000",
    "cache_minutes": 30
}
```

#### Clear Cache

```http
POST /customer/tracking/clear-cache
Content-Type: application/json

{
    "tracking_number": "9400100000000000000000"
}
```

## Service Methods

### UspsTrackingService

#### `trackSinglePackage($trackingNumber)`

Track một tracking number.

#### `trackMultiplePackages($trackingNumbers)`

Track nhiều tracking numbers (tối đa 35).

#### `checkDeliveryStatus($trackingNumber)`

Kiểm tra trạng thái giao hàng và phân tích status.

#### `trackWithCache($trackingNumber, $cacheMinutes = 30)`

Track với cache để tối ưu hiệu suất.

#### `clearTrackingCache($trackingNumber)`

Xóa cache cho tracking number.

## Response Format

### Success Response

```json
{
    "success": true,
    "data": {
        "packages": [
            {
                "tracking_number": "9400100000000000000000",
                "delivery_notification_date": "2024-01-15",
                "expected_delivery_date": "2024-01-16",
                "expected_delivery_time": "3:00 PM",
                "guaranteed_delivery_date": "2024-01-16",
                "track_summary": "Your item was delivered at 3:00 PM on January 16 in CITY STATE 12345.",
                "track_details": [
                    "January 16 3:00 PM DELIVERED CITY STATE 12345",
                    "January 16 2:30 PM OUT FOR DELIVERY CITY STATE 12345"
                ]
            }
        ]
    }
}
```

### Delivery Status Response

```json
{
    "success": true,
    "data": {
        "tracking_number": "9400100000000000000000",
        "status": "delivered",
        "is_delivered": true,
        "track_summary": "Your item was delivered at 3:00 PM on January 16 in CITY STATE 12345.",
        "expected_delivery_date": "2024-01-16",
        "track_details": [...],
        "full_response": {...}
    }
}
```

### Error Response

```json
{
    "success": false,
    "error": "Error message"
}
```

## Status Types

Service tự động phân tích trạng thái từ `track_summary`:

-   `delivered`: Đã giao hàng
-   `in_transit`: Đang vận chuyển
-   `out_for_delivery`: Đang giao hàng
-   `arrived_at_unit`: Đã đến đơn vị
-   `accepted`: Đã nhận
-   `unknown`: Không xác định

## Caching

Service sử dụng Laravel Cache để lưu kết quả tracking:

-   **Cache Key**: `usps_tracking_{tracking_number}`
-   **Default TTL**: 30 phút
-   **Max TTL**: 24 giờ

## Error Handling

Service xử lý các lỗi sau:

-   **Invalid tracking number**: Tracking number không hợp lệ
-   **API rate limit**: Vượt quá giới hạn API
-   **Network errors**: Lỗi kết nối
-   **Invalid response**: Response không hợp lệ từ USPS

## Logging

Tất cả các lỗi được log với context:

```php
Log::error('USPS Tracking Error', [
    'tracking_number' => $trackingNumber,
    'error' => $e->getMessage()
]);
```

## Giới hạn API

-   **Single request**: 1 tracking number
-   **Multiple request**: Tối đa 35 tracking numbers
-   **Rate limit**: Theo giới hạn của USPS API

## Troubleshooting

### Lỗi thường gặp

1. **"Invalid USERID"**

    - Kiểm tra USPS_USER_ID trong .env
    - Đảm bảo đã đăng ký USPS Web Tools

2. **"No record of that mail item"**

    - Tracking number không tồn tại
    - Package chưa được scan vào hệ thống

3. **"API rate limit exceeded"**
    - Giảm tần suất gọi API
    - Sử dụng cache để giảm requests

### Debug

Sử dụng command để debug:

```bash
php artisan usps:test-tracking TRACKING_NUMBER
```

## Security

-   Credentials được lưu trong environment variables
-   XML được escape để tránh injection
-   Validation input trước khi gửi request
-   Error messages không expose sensitive information
