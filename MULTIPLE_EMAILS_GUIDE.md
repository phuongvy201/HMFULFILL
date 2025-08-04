# Hướng dẫn sử dụng nhiều email cho User-Specific Pricing

## Tổng quan

Tính năng mới cho phép bạn import giá riêng cho nhiều user cùng lúc bằng cách sử dụng nhiều email trong một dòng CSV. Điều này giúp tiết kiệm thời gian khi cần áp dụng cùng một mức giá cho nhiều user.

## Cách sử dụng

### 1. Định dạng email trong CSV

Bạn có thể sử dụng một trong hai cách sau để phân tách nhiều email:

#### Cách 1: Sử dụng dấu phẩy (,)

```
user1@example.com,user2@example.com,user3@example.com
```

#### Cách 2: Sử dụng dấu chấm phẩy (;)

```
user1@example.com;user2@example.com;user3@example.com
```

### 2. Ví dụ file CSV

```csv
user_email,product_id,product_name,variant_sku,tiktok_1st,tiktok_next,seller_1st,seller_next,currency
user1@example.com,user2@example.com,1,Sample Product,SKU001,10.00,5.00,8.00,4.00,USD
user3@example.com;user4@example.com,2,Another Product,SKU002,15.00,7.50,12.00,6.00,USD
```

### 3. Sử dụng Command Line

```bash
# Import từ file CSV
php artisan import:user-pricing --file=path/to/file.csv

# Import với dry-run để xem preview
php artisan import:user-pricing --file=path/to/file.csv --dry-run
```

### 4. Sử dụng Web Interface

1. Vào trang Admin > User Specific Pricing Import
2. Upload file CSV với định dạng nhiều email
3. Hệ thống sẽ tự động parse và áp dụng giá cho tất cả user

## Lưu ý quan trọng

### Validation

-   Tất cả email phải có định dạng hợp lệ
-   Tất cả email phải tồn tại trong hệ thống
-   Nếu có bất kỳ email nào không hợp lệ, toàn bộ dòng sẽ bị bỏ qua

### Performance

-   Hệ thống sẽ tạo record riêng cho từng user
-   Mỗi dòng với N email sẽ tạo N records trong database
-   Không có giới hạn số lượng email trên một dòng, nhưng khuyến nghị không quá 50 email

### Error Handling

-   Nếu một email không tồn tại, hệ thống sẽ báo lỗi cụ thể
-   Nếu một email không hợp lệ, hệ thống sẽ báo lỗi cụ thể
-   Các email hợp lệ khác trong cùng dòng vẫn được xử lý

## Ví dụ thực tế

### Scenario 1: Áp dụng giá đặc biệt cho nhóm VIP

```csv
user_email,product_id,product_name,variant_sku,tiktok_1st,tiktok_next,seller_1st,seller_next,currency
vip1@company.com,vip2@company.com,vip3@company.com,1,Product A,SKU001,8.00,4.00,6.00,3.00,USD
```

### Scenario 2: Áp dụng giá cho nhóm đối tác

```csv
user_email,product_id,product_name,variant_sku,tiktok_1st,tiktok_next,seller_1st,seller_next,currency
partner1@example.com;partner2@example.com;partner3@example.com,2,Product B,SKU002,12.00,6.00,10.00,5.00,USD
```

## Troubleshooting

### Lỗi thường gặp

1. **"Email không hợp lệ"**

    - Kiểm tra định dạng email
    - Đảm bảo không có khoảng trắng thừa

2. **"Email không tồn tại trong hệ thống"**

    - Kiểm tra email đã được đăng ký chưa
    - Đảm bảo email chính xác (phân biệt hoa thường)

3. **"No valid users found"**
    - Tất cả email trong dòng đều không tồn tại
    - Cần kiểm tra lại danh sách email

### Tips

1. **Test với dry-run trước**

    ```bash
    php artisan import:user-pricing --file=test.csv --dry-run
    ```

2. **Sử dụng template**

    - Download template từ web interface
    - Template có sẵn ví dụ về nhiều email

3. **Kiểm tra email trước khi import**
    - Export danh sách user hiện tại
    - So sánh với danh sách email cần import

## API Changes

### Backward Compatibility

-   Vẫn hỗ trợ một email trên một dòng như trước
-   Không ảnh hưởng đến các tính năng hiện có

### New Features

-   Hỗ trợ nhiều email trên một dòng
-   Validation cải tiến cho nhiều email
-   Error reporting chi tiết hơn
-   Preview hiển thị số lượng email

## Migration Notes

Không cần migration database vì tính năng này chỉ thay đổi logic xử lý import, không thay đổi cấu trúc database.
