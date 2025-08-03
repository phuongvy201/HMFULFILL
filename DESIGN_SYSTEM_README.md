# Hệ thống Design Tasks

## Tổng quan

Hệ thống Design Tasks cho phép khách hàng tạo yêu cầu thiết kế và designer thực hiện thiết kế theo yêu cầu. Hệ thống bao gồm quản lý trạng thái, thanh toán và review thiết kế.

## Tính năng chính

### Cho Khách hàng (Customer)

-   ✅ Tạo yêu cầu thiết kế mới
-   ✅ Xem danh sách tasks của mình
-   ✅ Xem chi tiết task và timeline
-   ✅ Download files (mockup và design)
-   ✅ Review và phê duyệt thiết kế
-   ✅ Yêu cầu chỉnh sửa thiết kế

### Cho Designer

-   ✅ Xem danh sách tasks đang chờ
-   ✅ Nhận task để thực hiện
-   ✅ Upload file thiết kế hoàn chỉnh
-   ✅ Xem thống kê công việc
-   ✅ Quản lý tasks đã nhận

## Cấu trúc Database

### Bảng `design_tasks`

```sql
- id (primary key)
- customer_id (foreign key -> users)
- designer_id (foreign key -> users, nullable)
- title (string)
- description (text, nullable)
- sides_count (integer)
- price (decimal)
- status (enum: pending, joined, completed, approved, revision, cancelled)
- mockup_file (string, nullable)
- design_file (string, nullable) // Design gốc (phiên bản đầu tiên)
- revision_notes (text, nullable)
- completed_at (timestamp, nullable)
- created_at, updated_at
```

### Bảng `design_revisions`

```sql
- id (primary key)
- design_task_id (foreign key -> design_tasks)
- designer_id (foreign key -> users)
- design_file (string) // File thiết kế của revision
- notes (text, nullable) // Ghi chú của designer
- revision_notes (text, nullable) // Yêu cầu chỉnh sửa từ khách hàng
- version (integer) // Số phiên bản
- status (enum: submitted, approved, revision)
- submitted_at (timestamp)
- approved_at (timestamp, nullable)
- created_at, updated_at
```

## Trạng thái Tasks

| Trạng thái  | Mô tả                           | Màu sắc       |
| ----------- | ------------------------------- | ------------- |
| `pending`   | Chờ designer nhận               | 🟡 Vàng       |
| `joined`    | Designer đã nhận, đang thiết kế | 🔵 Xanh dương |
| `completed` | Hoàn thành thiết kế, chờ review | 🟢 Xanh lá    |
| `approved`  | Khách hàng đã phê duyệt         | 🟣 Tím        |
| `revision`  | Cần chỉnh sửa                   | 🔴 Đỏ         |
| `cancelled` | Đã hủy yêu cầu thiết kế         | ⚫ Xám        |

## Luồng hoạt động

### 1. Khách hàng tạo yêu cầu

```
Customer → Tạo yêu cầu → Upload mockup → Trừ tiền → Task pending
```

### 2. Designer nhận task

```
Designer → Xem tasks pending → Nhận task → Status: joined
```

### 3. Designer submit design

```
Designer → Upload design file → Status: completed
```

### 4. Khách hàng review

```
Customer → Xem design → Phê duyệt/Chỉnh sửa → Status: approved/revision
```

### 5. Designer chỉnh sửa (khi có yêu cầu revision)

```
Designer → Xem yêu cầu chỉnh sửa → Upload design mới → Status: completed
```

### 6. Khách hàng hủy yêu cầu (chỉ khi pending)

```
Customer → Hủy yêu cầu → Hoàn tiền → Status: cancelled
```

## Routes

### Customer Routes

```php
Route::get('/customer/design/create', 'create')->name('customer.design.create');
Route::post('/customer/design/store', 'store')->name('customer.design.store');
Route::get('/customer/design/my-tasks', 'myTasks')->name('customer.design.my-tasks');
Route::get('/customer/design/tasks/{taskId}', 'show')->name('customer.design.show');
Route::post('/customer/design/tasks/{taskId}/review', 'review')->name('customer.design.review');
Route::post('/customer/design/tasks/{taskId}/cancel', 'cancel')->name('customer.design.cancel');
```

### Designer Routes

```php
Route::get('/designer/tasks', 'designerTasks')->name('designer.tasks.index');
Route::post('/designer/tasks/{taskId}/join', 'joinTask')->name('designer.tasks.join');
Route::post('/designer/tasks/{taskId}/submit', 'submitDesign')->name('designer.tasks.submit');
Route::get('/designer/tasks/{taskId}', 'show')->name('designer.tasks.show');
```

## Models

### DesignTask Model

```php
// Constants
const STATUS_PENDING = 'pending';
const STATUS_JOINED = 'joined';
const STATUS_COMPLETED = 'completed';
const STATUS_APPROVED = 'approved';
const STATUS_REVISION = 'revision';
const STATUS_CANCELLED = 'cancelled';

// Methods
public function getStatusDisplayName(): string
public function isCompleted(): bool
public function canBeJoined(): bool
public static function calculatePrice(int $sidesCount): float
```

### User Model

```php
// Methods
public function getTotalBalance(): float
public function hasEnoughBalance(float $amount): bool
public function wallet(): HasOne
```

### Wallet Model

```php
// Methods
public function getTotalBalance(): float
public function hasEnoughBalance(float $amount): bool
public function withdraw(float $amount): bool
public function deposit(float $amount): bool
```

## Giao diện

### Trang My Design Tasks

-   Hiển thị danh sách tasks dạng card
-   Filter theo trạng thái
-   Modal review thiết kế
-   Pagination

### Trang Tạo yêu cầu

-   Form tạo yêu cầu
-   Upload file mockup
-   Tính giá tự động
-   Validation

### Trang Chi tiết Task

-   Thông tin chi tiết task
-   Timeline quá trình
-   Download files
-   Review thiết kế

### Designer Dashboard

-   Thống kê tasks
-   Tabs: Tasks đang chờ / Tasks của tôi
-   Modal submit design
-   Join task functionality

## Tính năng kỹ thuật

### File Upload

-   Hỗ trợ: JPG, JPEG, PNG, PDF, AI, PSD
-   Giới hạn: 50MB per file
-   Storage: AWS S3

### Payment Integration

-   Tích hợp với hệ thống ví
-   Trừ tiền tự động khi tạo task
-   Validation số dư

### Security

-   CSRF protection
-   File validation
-   Role-based access control
-   Transaction safety

### Performance

-   Eager loading relationships
-   Pagination cho danh sách lớn
-   Optimized queries

## Cài đặt và Sử dụng

### 1. Migration

```bash
php artisan migrate
```

### 2. Seeder (nếu cần)

```bash
php artisan db:seed --class=DesignTaskSeeder
```

### 3. Storage Setup

```bash
php artisan storage:link
```

### 4. AWS S3 Configuration

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=your_region
AWS_BUCKET=your_bucket
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## Troubleshooting

### Lỗi thường gặp

1. **Method hasPages() does not exist**

    - Sử dụng `paginate()` thay vì `get()`
    - Kiểm tra instance type trước khi gọi `hasPages()`

2. **File upload failed**

    - Kiểm tra S3 credentials
    - Kiểm tra file permissions
    - Validate file size và type

3. **Insufficient balance**

    - Kiểm tra số dư trước khi tạo task
    - Sử dụng `hasEnoughBalance()` method

4. **Task already taken**
    - Sử dụng database locking để tránh race condition
    - Kiểm tra status trước khi join

## Contributing

Khi thêm tính năng mới:

1. Tạo migration nếu cần thay đổi database
2. Cập nhật model với relationships và methods
3. Thêm routes mới
4. Tạo controller methods
5. Tạo views với Tailwind CSS
6. Thêm validation và error handling
7. Test thoroughly

## License

Hệ thống này là một phần của dự án Fulfill-HM.
