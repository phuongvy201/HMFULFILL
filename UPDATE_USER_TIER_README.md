# Hướng dẫn Cập nhật User Tier

## Tổng quan

Hệ thống cung cấp 3 commands để cập nhật tier cho user và các giá shipping liên quan:

1. **`user:update-to-special-tier`** - Command tổng hợp (khuyến nghị sử dụng)
2. **`shipping:update-user-tier`** - Cập nhật giá shipping cho user
3. **`excel-order:update-fulfillment-prices`** - Cập nhật giá trong orders hiện có

## 1. Command Tổng hợp (Khuyến nghị)

### Cú pháp:

```bash
php artisan user:update-to-special-tier {user_id} [options]
```

### Ví dụ:

```bash
# Xem trước thay đổi (dry run)
php artisan user:update-to-special-tier 123 --dry-run

# Thực hiện cập nhật tier và giá shipping
php artisan user:update-to-special-tier 123

# Thực hiện cập nhật tier, giá shipping và orders hiện có
php artisan user:update-to-special-tier 123 --update-existing-orders
```

### Options:

-   `--dry-run`: Chỉ hiển thị thay đổi mà không thực hiện
-   `--update-existing-orders`: Cập nhật cả orders hiện có

## 2. Cập nhật Giá Shipping

### Cú pháp:

```bash
php artisan shipping:update-user-tier {user_id} {from_tier} {to_tier} [options]
```

### Ví dụ:

```bash
# Cập nhật từ Wood sang Special
php artisan shipping:update-user-tier 123 Wood Special

# Xem trước thay đổi
php artisan shipping:update-user-tier 123 Wood Special --dry-run

# Cập nhật từ Silver sang Gold
php artisan shipping:update-user-tier 123 Silver Gold
```

### Tham số:

-   `user_id`: ID của user cần cập nhật
-   `from_tier`: Tier hiện tại (mặc định: Wood)
-   `to_tier`: Tier mới (mặc định: Special)

### Options:

-   `--dry-run`: Chỉ hiển thị thay đổi mà không thực hiện

## 3. Cập nhật Orders Hiện Có

### Cú pháp:

```bash
php artisan excel-order:update-fulfillment-prices {user_id} {from_tier} {to_tier} [options]
```

### Ví dụ:

```bash
# Cập nhật tất cả orders của user
php artisan excel-order:update-fulfillment-prices 123 Wood Special

# Chỉ cập nhật order cụ thể
php artisan excel-order:update-fulfillment-prices 123 Wood Special --order-id=456

# Xem trước thay đổi
php artisan excel-order:update-fulfillment-prices 123 Wood Special --dry-run
```

### Options:

-   `--dry-run`: Chỉ hiển thị thay đổi mà không thực hiện
-   `--order-id`: Chỉ cập nhật order cụ thể

## Cách Hoạt Động

### Logic Ưu Tiên Giá:

1. **User-specific price** (cao nhất) - Giá riêng cho user cụ thể
2. **Tier-specific price** - Giá theo tier của user
3. **Base price** (Wood tier) - Giá mặc định

### Quy Trình Cập Nhật:

1. **Cập nhật tier** trong bảng `user_tiers`
2. **Tạo/cập nhật user-specific overrides** trong bảng `shipping_overrides`
3. **Cập nhật giá trong orders** (nếu được yêu cầu)

## Ví Dụ Thực Tế

### Tình huống: Cập nhật user ID 123 từ Wood sang Special tier

```bash
# Bước 1: Xem trước thay đổi
php artisan user:update-to-special-tier 123 --dry-run

# Bước 2: Thực hiện cập nhật
php artisan user:update-to-special-tier 123 --update-existing-orders
```

### Kết quả:

-   User được cập nhật lên Special tier
-   Tất cả giá shipping được cập nhật theo Special tier
-   Orders hiện có được cập nhật với giá mới
-   User-specific overrides được tạo trong `shipping_overrides`

## Lưu Ý Quan Trọng

1. **Luôn chạy `--dry-run` trước** để xem thay đổi
2. **Backup database** trước khi thực hiện
3. **Kiểm tra kỹ** user_id trước khi chạy
4. **Logs** được lưu trong `storage/logs/laravel.log`

## Troubleshooting

### Lỗi thường gặp:

1. **User không tồn tại:**

    ```
    User với ID 123 không tồn tại!
    ```

2. **Không tìm thấy giá tier:**

    ```
    Không tìm thấy giá cho tier 'Special' trong shipping price ID: 456
    ```

3. **Lỗi database:**
    - Kiểm tra logs trong `storage/logs/laravel.log`
    - Đảm bảo có quyền write vào database

### Khôi phục:

-   Sử dụng backup database
-   Hoặc chạy lại command với tier cũ

## Cấu Trúc Database

### Bảng liên quan:

-   `users`: Thông tin user
-   `user_tiers`: Tier của user theo tháng
-   `shipping_prices`: Giá shipping cơ bản (Wood tier)
-   `shipping_overrides`: Giá override cho tier và user
-   `excel_orders`: Orders của user
-   `excel_order_items`: Items trong orders
-   `excel_order_fulfillments`: Thông tin fulfillment
