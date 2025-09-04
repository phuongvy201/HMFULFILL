# Hệ thống Quản lý Design Tasks dành cho Admin

## Tổng quan

Hệ thống quản lý design tasks dành cho admin cung cấp giao diện toàn diện để quản lý, theo dõi và xuất báo cáo về các design tasks trong hệ thống.

## Tính năng chính

### 1. Dashboard thống kê

-   **Thống kê tổng quan**: Tổng số tasks, tasks đang chờ, hoàn thành, đã duyệt
-   **Biểu đồ theo tháng**: Thống kê số lượng tasks và doanh thu theo tháng
-   **Top designers**: Danh sách designers có hiệu suất cao nhất
-   **Tasks gần đây**: Danh sách các tasks mới nhất
-   **Chỉ số hiệu suất**: Tỷ lệ hoàn thành, đang làm, chờ xử lý

### 2. Quản lý danh sách tasks

-   **Xem danh sách**: Hiển thị tất cả design tasks với phân trang
-   **Bộ lọc nâng cao**:
    -   Lọc theo trạng thái (pending, joined, completed, approved, revision, cancelled)
    -   Lọc theo designer
    -   Lọc theo khách hàng
    -   Lọc theo khoảng thời gian
    -   Lọc theo khoảng giá
-   **Tìm kiếm**: Tìm kiếm nhanh theo tiêu đề, mô tả

### 3. Thao tác với tasks

-   **Xem chi tiết**: Xem đầy đủ thông tin task, files, comments
-   **Thay đổi trạng thái**: Cập nhật trạng thái task
-   **Xóa task**: Xóa task không cần thiết
-   **Hành động hàng loạt**: Xóa hoặc thay đổi trạng thái nhiều tasks cùng lúc

### 4. Xuất báo cáo CSV

-   **Xuất theo bộ lọc**: Xuất CSV dựa trên các bộ lọc đã áp dụng
-   **Thông tin đầy đủ**: Bao gồm tất cả thông tin quan trọng của tasks
-   **Định dạng chuẩn**: File CSV có thể mở bằng Excel, Google Sheets

## Cấu trúc file

```
app/Http/Controllers/Admin/
└── DesignManagementController.php    # Controller chính

resources/views/admin/design/
├── index.blade.php                   # Trang danh sách tasks
├── dashboard.blade.php               # Trang dashboard thống kê
└── show.blade.php                    # Trang chi tiết task

routes/
└── web.php                          # Routes cho admin design
```

## Routes

| Route                        | Method | Mô tả                    |
| ---------------------------- | ------ | ------------------------ |
| `/admin/design`              | GET    | Trang danh sách tasks    |
| `/admin/design/dashboard`    | GET    | Trang dashboard thống kê |
| `/admin/design/export-csv`   | GET    | Xuất CSV                 |
| `/admin/design/{id}`         | GET    | Xem chi tiết task        |
| `/admin/design/{id}/status`  | PUT    | Cập nhật trạng thái      |
| `/admin/design/{id}`         | DELETE | Xóa task                 |
| `/admin/design/bulk-actions` | POST   | Hành động hàng loạt      |

## Hướng dẫn sử dụng

### 1. Truy cập hệ thống

-   Đăng nhập với tài khoản admin
-   Truy cập `/admin/design` để xem danh sách tasks
-   Truy cập `/admin/design/dashboard` để xem thống kê

### 2. Quản lý tasks

-   **Xem danh sách**: Sử dụng các bộ lọc để tìm kiếm tasks cụ thể
-   **Thay đổi trạng thái**: Click vào icon edit để mở modal thay đổi trạng thái
-   **Xem chi tiết**: Click vào icon eye để xem thông tin chi tiết task
-   **Xóa task**: Click vào icon trash để xóa task

### 3. Hành động hàng loạt

-   Chọn các tasks cần thao tác bằng checkbox
-   Chọn hành động (xóa hoặc thay đổi trạng thái)
-   Click "Thực hiện" để áp dụng hành động

### 4. Xuất CSV

-   Áp dụng các bộ lọc cần thiết
-   Click nút "Xuất CSV" để tải file
-   File sẽ được tải về với tên `design_tasks_YYYY-MM-DD_HH-MM-SS.csv`

## Cấu trúc dữ liệu CSV

File CSV xuất ra bao gồm các cột:

| Cột           | Mô tả                   |
| ------------- | ----------------------- |
| ID            | ID của task             |
| Tiêu đề       | Tiêu đề task            |
| Mô tả         | Mô tả chi tiết          |
| Trạng thái    | Trạng thái hiện tại     |
| Giá ($)       | Giá của task            |
| Số mặt        | Số mặt cần thiết kế     |
| Khách hàng    | Tên khách hàng          |
| Designer      | Tên designer (nếu có)   |
| Sản phẩm      | Tên sản phẩm            |
| Ngày tạo      | Ngày tạo task           |
| Ngày cập nhật | Ngày cập nhật cuối      |
| Ghi chú       | Ghi chú bổ sung         |
| File mockup   | Có/Không có file mockup |
| File design   | Có/Không có file design |

## Bảo mật

-   Tất cả routes đều được bảo vệ bởi middleware `auth` và `admin`
-   Chỉ admin mới có thể truy cập các chức năng này
-   CSRF protection được áp dụng cho tất cả form và AJAX requests

## Tùy chỉnh

### Thêm bộ lọc mới

1. Thêm logic filter trong `DesignManagementController@index`
2. Thêm form field trong view `index.blade.php`
3. Cập nhật logic xuất CSV nếu cần

### Thêm thống kê mới

1. Thêm logic tính toán trong `DesignManagementController@dashboard`
2. Thêm card hoặc biểu đồ trong view `dashboard.blade.php`

### Thêm hành động mới

1. Thêm method trong controller
2. Thêm route mới
3. Cập nhật view và JavaScript

## Troubleshooting

### Lỗi thường gặp

1. **Không thể truy cập trang**

    - Kiểm tra quyền admin
    - Kiểm tra middleware đã được đăng ký

2. **Không thể xuất CSV**

    - Kiểm tra quyền ghi file
    - Kiểm tra memory limit cho file lớn

3. **Biểu đồ không hiển thị**
    - Kiểm tra Chart.js đã được load
    - Kiểm tra console errors

### Debug

-   Sử dụng Laravel logs để debug
-   Kiểm tra database queries
-   Sử dụng browser developer tools để debug JavaScript

## Liên hệ hỗ trợ

Nếu gặp vấn đề hoặc cần hỗ trợ, vui lòng liên hệ team development.
