# Hướng dẫn Import Giá Riêng Cho User

## 📋 **Tổng quan**

Hệ thống cho phép import giá riêng cho user từ file CSV hoặc Excel (.xlsx, .xls) một cách dễ dàng và nhanh chóng.

## 📁 **Cấu trúc file CSV/Excel**

File CSV hoặc Excel phải có các cột sau:

| Cột            | Mô tả                   | Ví dụ                          |
| -------------- | ----------------------- | ------------------------------ |
| `user_email`   | Email của user          | `john.doe@example.com`         |
| `product_id`   | ID của sản phẩm         | `123`                          |
| `product_name` | Tên sản phẩm            | `Product Name`                 |
| `variant_sku`  | SKU của product variant | `PROD-001` (có thể để trống)   |
| `tiktok_1st`   | Giá cho TikTok 1st      | `15.99` (có thể để trống)      |
| `tiktok_next`  | Giá cho TikTok Next     | `12.50` (có thể để trống)      |
| `seller_1st`   | Giá cho Seller 1st      | `18.99` (có thể để trống)      |
| `seller_next`  | Giá cho Seller Next     | `14.50` (có thể để trống)      |
| `currency`     | Đơn vị tiền tệ          | `USD`, `VND`, `GBP`            |
| `attr_name_1`  | Tên thuộc tính 1        | `color`, `size`, `material`    |
| `attr_value_1` | Giá trị thuộc tính 1    | `Black`, `M`, `Cotton`         |
| `attr_name_2`  | Tên thuộc tính 2        | `size`, `style`, `pattern`     |
| `attr_value_2` | Giá trị thuộc tính 2    | `L`, `Sport`, `Striped`        |
| `attr_name_3`  | Tên thuộc tính 3        | `material`, `style`, `pattern` |
| `attr_value_3` | Giá trị thuộc tính 3    | `Polyester`, `Casual`, `Solid` |

### Ví dụ file CSV/Excel:

**CSV Format:**

```csv
user_email,product_id,product_name,variant_sku,tiktok_1st,tiktok_next,seller_1st,seller_next,currency,attr_name_1,attr_value_1,attr_name_2,attr_value_2,attr_name_3,attr_value_3
john.doe@example.com,123,Product Name 1,PROD-001,15.99,12.50,18.99,14.50,USD,color,Black,size,M,material,Cotton
jane.smith@example.com,456,Product Name 2,PROD-002,12.50,10.00,16.50,13.00,USD,color,White,size,L,style,Sport
vip.customer@example.com,789,Product Name 3,,10.99,8.75,14.99,11.75,USD,color,Red,size,XL,material,Cotton
```

**Excel Format (.xlsx):**
Cùng cấu trúc như CSV, nhưng lưu dưới định dạng Excel với header ở dòng đầu tiên.

## 🚀 **Cách sử dụng**

### 1. Download Template

```bash
GET /api/user-specific-pricing-import/template
```

### 2. Import từ CSV/Excel

```bash
POST /api/user-specific-pricing-import/csv
Content-Type: multipart/form-data

file: [file CSV hoặc Excel]
```

### 3. Preview trước khi import

```bash
POST /api/user-specific-pricing-import/preview
Content-Type: multipart/form-data

file: [file CSV hoặc Excel]
```

### 4. Import hàng loạt từ JSON

```bash
POST /api/user-specific-pricing-import/batch
Content-Type: application/json

{
  "prices": [
    {
      "user_id": 123,
      "variant_id": 456,
      "tiktok_1st": 15.99,
      "tiktok_next": 12.50,
      "seller_1st": 18.99,
      "seller_next": 14.50,
      "currency": "USD"
    }
  ]
}
```

## 📊 **API Endpoints**

### User-Specific Pricing API Routes

```php
// Lấy giá riêng của user
GET /api/user-specific-pricing/{userId}/{variantId}/{method}

// Lấy tất cả giá riêng của user
GET /api/user-specific-pricing/{userId}

// Tạo giá riêng cho user
POST /api/user-specific-pricing

// Cập nhật giá riêng cho user
PUT /api/user-specific-pricing/{userId}/{variantId}/{method}

// Xóa giá riêng cho user
DELETE /api/user-specific-pricing/{userId}/{variantId}/{method}

// Copy giá từ user này sang user khác
POST /api/user-specific-pricing/copy
```

### User-Specific Pricing Import API Routes

```php
// Lấy dữ liệu hỗ trợ import
GET /api/user-specific-pricing-import/data

// Import từ CSV
POST /api/user-specific-pricing-import/csv

// Preview CSV trước khi import
POST /api/user-specific-pricing-import/preview

// Import từ form
POST /api/user-specific-pricing-import/form

// Import hàng loạt từ JSON
POST /api/user-specific-pricing-import/batch

// Export giá của user
GET /api/user-specific-pricing-import/export/user/{userId}

// Export tất cả giá
GET /api/user-specific-pricing-import/export/all

// Download template
GET /api/user-specific-pricing-import/template
```

## 🔧 **Sử dụng trong code**

### Import từ dữ liệu array:

```php
use App\Services\UserSpecificPricingImportService;

$data = [
    [
        'user_email' => 'john.doe@example.com',
        'product_id' => 123,
        'product_name' => 'Product Name 1',
        'variant_sku' => 'PROD-001',
        'tiktok_1st' => 15.99,
        'tiktok_next' => 12.50,
        'seller_1st' => 18.99,
        'seller_next' => 14.50,
        'currency' => 'USD',
        'attr_name_1' => 'color',
        'attr_value_1' => 'Black',
        'attr_name_2' => 'size',
        'attr_value_2' => 'M',
        'attr_name_3' => 'material',
        'attr_value_3' => 'Cotton'
    ],
    [
        'user_email' => 'jane.smith@example.com',
        'product_id' => 456,
        'product_name' => 'Product Name 2',
        'variant_sku' => 'PROD-002',
        'tiktok_1st' => 12.50,
        'tiktok_next' => 10.00,
        'seller_1st' => 16.50,
        'seller_next' => 13.00,
        'currency' => 'USD',
        'attr_name_1' => 'color',
        'attr_value_1' => 'White',
        'attr_name_2' => 'size',
        'attr_value_2' => 'L',
        'attr_name_3' => 'style',
        'attr_value_3' => 'Sport'
    ]
];

$results = UserSpecificPricingImportService::importFromData($data);

echo "Success: {$results['success']}, Failed: {$results['failed']}";
```

### Validate dữ liệu:

```php
$errors = UserSpecificPricingImportService::validateImportData($data);

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}
```

### Parse CSV/Excel file:

```php
$file = $request->file('file');
$extension = strtolower($file->getClientOriginalExtension());

if (in_array($extension, ['xlsx', 'xls'])) {
    $data = UserSpecificPricingImportService::parseExcelFile($file);
} else {
    $data = UserSpecificPricingImportService::parseCsvFile($file);
}
```

## ✅ **Validation Rules**

### File CSV/Excel:

-   File phải có định dạng CSV hoặc Excel (.xlsx, .xls)
-   Kích thước tối đa: 10MB
-   Phải có header với các cột bắt buộc

### Dữ liệu:

-   `user_email`: Email phải tồn tại trong database
-   `product_id`: ID sản phẩm phải tồn tại trong database
-   `product_name`: Tên sản phẩm (để tham khảo)
-   `variant_sku`: SKU phải tồn tại trong database và thuộc về sản phẩm đã chỉ định (có thể để trống nếu có attributes)
-   `attr_name_1`, `attr_value_1`, `attr_name_2`, `attr_value_2`, `attr_name_3`, `attr_value_3`: Các cặp tên-giá trị thuộc tính sản phẩm (có thể để trống)
-   `tiktok_1st`, `tiktok_next`, `seller_1st`, `seller_next`: Phải là số dương hoặc để trống
-   `currency`: Phải là một trong các giá trị: `USD`, `VND`, `GBP`

## 📈 **Kết quả import**

```json
{
    "success": true,
    "message": "Import completed. Success: 5, Failed: 1",
    "data": {
        "success": 5,
        "failed": 1,
        "errors": [
            {
                "row": 3,
                "errors": {
                    "user_email": ["User not found"]
                },
                "data": {
                    "user_email": "invalid@example.com",
                    "variant_sku": "PROD-001",
                    "tiktok_1st": "15.99",
                    "tiktok_next": "12.50",
                    "seller_1st": "18.99",
                    "seller_next": "14.50",
                    "currency": "USD"
                }
            }
        ],
        "summary": {
            "total_rows": 6,
            "processed_users": {
                "123": {
                    "user_email": "john.doe@example.com",
                    "user_name": "John Doe",
                    "count": 2
                },
                "456": {
                    "user_email": "jane.smith@example.com",
                    "user_name": "Jane Smith",
                    "count": 3
                }
            }
        }
    }
}
```

## 🎯 **Ví dụ thực tế**

### Ví dụ 1: Import giá VIP cho khách hàng

```csv
user_email,product_id,product_name,variant_sku,tiktok_1st,tiktok_next,seller_1st,seller_next,currency,attr_name_1,attr_value_1,attr_name_2,attr_value_2,attr_name_3,attr_value_3
vip.customer@example.com,123,Product Name 1,PROD-001,12.99,8.50,14.99,9.50,USD,color,Black,size,M,material,Cotton
vip.customer@example.com,456,Product Name 2,PROD-002,11.99,7.50,13.99,8.50,USD,color,White,size,L,style,Sport
```

### Ví dụ 2: Import giá bulk cho nhiều khách hàng

```csv
user_email,product_id,product_name,variant_sku,tiktok_1st,tiktok_next,seller_1st,seller_next,currency,attr_name_1,attr_value_1,attr_name_2,attr_value_2,attr_name_3,attr_value_3
bulk.buyer1@example.com,123,Product Name 1,PROD-001,10.99,8.50,12.99,9.50,USD,color,Black,size,M,material,Cotton
bulk.buyer1@example.com,456,Product Name 2,PROD-002,11.99,9.50,13.99,10.50,USD,color,White,size,L,style,Sport
bulk.buyer2@example.com,123,Product Name 1,,11.50,9.00,13.50,10.00,USD,color,Red,size,XL,material,Cotton
bulk.buyer2@example.com,456,Product Name 2,PROD-002,12.50,10.00,14.50,11.00,USD,color,Blue,size,S,style,Sport
```

## ⚠️ **Lưu ý quan trọng**

1. **Thứ tự ưu tiên**: User-specific price sẽ override tất cả các loại giá khác
2. **Duplicate handling**: Nếu đã có giá riêng cho user + variant + method, sẽ update giá mới
3. **Validation**: Tất cả dữ liệu sẽ được validate trước khi import
4. **Logging**: Tất cả thao tác import sẽ được log lại
5. **Error handling**: Nếu có lỗi ở một dòng, các dòng khác vẫn được xử lý
6. **Performance**: Có thể import hàng nghìn dòng một lần

## 🔍 **Troubleshooting**

### Lỗi thường gặp:

1. **"User not found"**: Kiểm tra email user có đúng không
2. **"Product not found"**: Kiểm tra product_id có đúng không
3. **"Product variant not found for this product or attributes"**: Kiểm tra SKU variant hoặc attributes có thuộc về sản phẩm đã chỉ định không
4. **"Price phải là số dương"**: Kiểm tra giá có đúng định dạng không
5. **"Currency không hợp lệ"**: Kiểm tra currency có đúng không
6. **"Phải có ít nhất một giá được cung cấp"**: Đảm bảo có ít nhất một cột giá được điền

### Debug:

```php
// Kiểm tra user có tồn tại không
$user = User::where('email', 'test@example.com')->first();

// Kiểm tra product có tồn tại không
$product = Product::find(123);

// Kiểm tra variant có thuộc về product không
$variant = ProductVariant::where('sku', 'PROD-001')
    ->where('product_id', 123)
    ->first();

// Hoặc tìm variant theo attributes
$selectedAttributes = ['color' => 'Black', 'size' => 'M'];
$variant = ProductVariant::findVariantByAttributes(123, $selectedAttributes);

// Hoặc tìm variant theo attributes từ CSV
$attrNames = ['color', 'size', 'print side'];
$attrValues = ['Black', 'M', 'Cotton'];
$selectedAttributes = array_combine($attrNames, $attrValues);
$variant = ProductVariant::findVariantByAttributes(123, $selectedAttributes);

// Kiểm tra các method có hợp lệ không
$validMethods = ['tiktok_1st', 'tiktok_next', 'seller_1st', 'seller_next'];
```
