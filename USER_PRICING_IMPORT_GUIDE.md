# 📋 Hướng dẫn Import User Pricing

## 🎯 Tổng quan

Hệ thống cho phép import giá shipping riêng cho từng user thông qua file Excel. Giá này sẽ được ưu tiên cao nhất khi user đặt hàng.

## 🚀 Cách sử dụng

### **1. Truy cập giao diện**

-   **URL:** `/admin/user-pricing`
-   **Import:** `/admin/user-pricing/import`

### **2. Cấu trúc file Excel**

| Cột | Tên               | Mô tả                                        | Ví dụ     | Bắt buộc |
| --- | ----------------- | -------------------------------------------- | --------- | -------- |
| A   | User ID           | ID của user (số)                             | 123       | ✅       |
| B   | Product Name      | Tên sản phẩm (để tham khảo)                  | Product A | ✅       |
| C   | Variant SKU       | SKU của variant                              | SKU001    | ✅       |
| D   | TikTok 1st Price  | Giá TikTok 1st (số, để 0 nếu không áp dụng)  | 12.50     | ❌       |
| E   | TikTok Next Price | Giá TikTok Next (số, để 0 nếu không áp dụng) | 15.00     | ❌       |
| F   | Seller 1st Price  | Giá Seller 1st (số, để 0 nếu không áp dụng)  | 18.75     | ❌       |
| G   | Seller Next Price | Giá Seller Next (số, để 0 nếu không áp dụng) | 20.00     | ❌       |
| H   | Currency          | Loại tiền tệ                                 | USD       | ❌       |

### **3. Hỗ trợ nhiều User ID**

Bạn có thể nhập nhiều user ID trong một dòng:

```
User ID: 123,456,789
User ID: 123;456;789
```

### **4. Shipping Methods được hỗ trợ**

-   `tiktok_1st` - TikTok shipping item đầu tiên
-   `tiktok_next` - TikTok shipping item tiếp theo
-   `seller_1st` - Seller shipping item đầu tiên
-   `seller_next` - Seller shipping item tiếp theo

### **5. Ví dụ dữ liệu**

```excel
User ID | Product Name | Variant SKU | TikTok 1st | TikTok Next | Seller 1st | Seller Next | Currency
123,456 | Product A    | SKU001       | 12.50      | 15.00       | 18.75      | 20.00       | USD
123;456 | Product B    | SKU002       | 10.00      | 13.50       | 16.25      | 18.50       | USD
456     | Product C    | SKU003       | 8.75       | 0           | 12.00      | 0           | USD
```

## 🔧 Sử dụng Command Line

### **Import từ file:**

```bash
php artisan import:user-pricing path/to/file.xlsx
```

### **Kiểm tra file (dry-run):**

```bash
php artisan import:user-pricing path/to/file.xlsx --dry-run
```

## 📊 Logic hoạt động

### **Thứ tự ưu tiên giá:**

1. **User-specific price** (cao nhất) - Giá riêng cho user
2. **Tier-specific price** - Giá theo tier của user
3. **Base price** - Giá mặc định

### **Ví dụ:**

-   User 123 có giá riêng: 15.50 USD
-   User 123 thuộc tier Gold: 18.00 USD
-   Giá mặc định: 12.50 USD

→ Kết quả: User 123 sẽ được áp dụng giá **15.50 USD**

## 🛠️ API Endpoints

### **Import file:**

```
POST /admin/user-pricing/import
Content-Type: multipart/form-data

excel_file: [file]
```

### **Download template:**

```
GET /admin/user-pricing/template
```

### **Xem danh sách:**

```
GET /admin/user-pricing
```

## 📝 Validation Rules

### **User ID:**

-   Phải tồn tại trong database
-   Hỗ trợ nhiều ID: `123,456,789` hoặc `123;456;789`
-   Không được rỗng

### **Variant SKU:**

-   Phải tồn tại trong database
-   Tìm kiếm theo: `sku`, `twofifteen_sku`, `flashship_sku`

### **Price (TikTok 1st, TikTok Next, Seller 1st, Seller Next):**

-   Phải là số >= 0
-   Để 0 nếu không muốn áp dụng giá cho method đó
-   Ít nhất một giá phải > 0
-   Hỗ trợ decimal: 12.50, 15.75

### **Currency:**

-   Mặc định: USD
-   Hỗ trợ: USD, VND, GBP

## 🔍 Troubleshooting

### **Lỗi thường gặp:**

1. **"User ID không tồn tại"**

    - Kiểm tra user có tồn tại trong database không
    - Đảm bảo User ID là số nguyên

2. **"Variant SKU không tồn tại"**

    - Kiểm tra SKU có đúng không
    - Tìm kiếm trong: `sku`, `twofifteen_sku`, `flashship_sku`

3. **"Ít nhất một giá phải lớn hơn 0"**

    - Đảm bảo ít nhất một trong 4 cột giá > 0
    - Có thể để 0 cho các method không muốn áp dụng

4. **"Price phải là số >= 0"**
    - Đảm bảo giá >= 0
    - Sử dụng dấu chấm (.) thay vì dấu phẩy (,)

### **Log files:**

```
storage/logs/laravel.log
```

## 📈 Monitoring

### **Kiểm tra import thành công:**

1. Vào `/admin/user-pricing`
2. Xem danh sách user pricing đã import
3. Kiểm tra giá override có đúng không

### **Test với order:**

1. Tạo order với user có pricing riêng
2. Kiểm tra giá shipping được áp dụng
3. Verify trong log: `Used override shipping price`

## 🎯 Best Practices

1. **Backup trước khi import lớn**
2. **Test với file nhỏ trước**
3. **Kiểm tra template trước khi import**
4. **Validate dữ liệu trước khi upload**
5. **Monitor log sau khi import**

## 📞 Support

Nếu gặp vấn đề, vui lòng:

1. Kiểm tra log file
2. Verify dữ liệu trong file Excel
3. Test với command line trước
4. Liên hệ admin nếu cần hỗ trợ
