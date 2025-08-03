# User-Specific Pricing API Endpoints

## 🔐 **Authentication**

Tất cả API endpoints đều yêu cầu authentication thông qua middleware `auth.api.token`.

## 📋 **API Endpoints Summary**

### **1. User-Specific Pricing Management**

| Method   | Endpoint                                                   | Description                         |
| -------- | ---------------------------------------------------------- | ----------------------------------- |
| `GET`    | `/api/user-specific-pricing/{userId}/{variantId}/{method}` | Lấy giá riêng của user              |
| `GET`    | `/api/user-specific-pricing/{userId}`                      | Lấy tất cả giá riêng của user       |
| `POST`   | `/api/user-specific-pricing`                               | Tạo giá riêng cho user              |
| `PUT`    | `/api/user-specific-pricing/{userId}/{variantId}/{method}` | Cập nhật giá riêng cho user         |
| `DELETE` | `/api/user-specific-pricing/{userId}/{variantId}/{method}` | Xóa giá riêng cho user              |
| `POST`   | `/api/user-specific-pricing/copy`                          | Copy giá từ user này sang user khác |

### **2. User-Specific Pricing Import**

| Method | Endpoint                                                 | Description                  |
| ------ | -------------------------------------------------------- | ---------------------------- |
| `GET`  | `/api/user-specific-pricing-import/data`                 | Lấy dữ liệu hỗ trợ import    |
| `POST` | `/api/user-specific-pricing-import/csv`                  | Import từ CSV                |
| `POST` | `/api/user-specific-pricing-import/preview`              | Preview CSV trước khi import |
| `POST` | `/api/user-specific-pricing-import/form`                 | Import từ form               |
| `POST` | `/api/user-specific-pricing-import/batch`                | Import hàng loạt từ JSON     |
| `GET`  | `/api/user-specific-pricing-import/export/user/{userId}` | Export giá của user          |
| `GET`  | `/api/user-specific-pricing-import/export/all`           | Export tất cả giá            |
| `GET`  | `/api/user-specific-pricing-import/template`             | Download template            |

## 📝 **Request/Response Examples**

### **1. Lấy giá riêng của user**

```bash
GET /api/user-specific-pricing/123/456/seller_1st
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 123,
        "variant_id": 456,
        "method": "seller_1st",
        "price": 15.99,
        "currency": "USD",
        "user": {
            "id": 123,
            "email": "john.doe@example.com",
            "first_name": "John",
            "last_name": "Doe"
        },
        "variant": {
            "id": 456,
            "sku": "PROD-001",
            "product": {
                "id": 789,
                "name": "Product Name"
            }
        }
    }
}
```

### **2. Tạo giá riêng cho user**

```bash
POST /api/user-specific-pricing
Content-Type: application/json

{
  "user_id": 123,
  "variant_id": 456,
  "method": "seller_1st",
  "price": 15.99,
  "currency": "USD"
}
```

**Response:**

```json
{
    "success": true,
    "message": "User-specific price created successfully",
    "data": {
        "id": 1,
        "user_id": 123,
        "variant_id": 456,
        "method": "seller_1st",
        "price": 15.99,
        "currency": "USD"
    }
}
```

### **3. Import từ CSV/Excel**

```bash
POST /api/user-specific-pricing-import/csv
Content-Type: multipart/form-data

file: [file CSV hoặc Excel]
```

**Response:**

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
                }
            }
        }
    }
}
```

### **4. Copy giá từ user này sang user khác**

```bash
POST /api/user-specific-pricing/copy
Content-Type: application/json

{
  "from_user_id": 123,
  "to_user_id": 456
}
```

**Response:**

```json
{
    "success": true,
    "message": "Successfully copied 5 prices",
    "copied_count": 5
}
```

## 🔧 **Error Responses**

### **Validation Error**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "user_id": ["The user id field is required."],
        "price": ["The price must be a number."]
    }
}
```

### **Not Found Error**

```json
{
    "success": false,
    "message": "User-specific price not found"
}
```

### **Server Error**

```json
{
    "success": false,
    "message": "Failed to create user-specific price: User not found with ID: 999"
}
```

## 📊 **Status Codes**

| Status Code | Description                    |
| ----------- | ------------------------------ |
| `200`       | Success                        |
| `201`       | Created                        |
| `400`       | Bad Request (Validation Error) |
| `401`       | Unauthorized                   |
| `404`       | Not Found                      |
| `422`       | Unprocessable Entity           |
| `500`       | Internal Server Error          |

## 🔍 **Query Parameters**

### **Lấy tất cả giá riêng của user**

```bash
GET /api/user-specific-pricing/123?include=user,variant,product
```

### **Lấy dữ liệu hỗ trợ import**

```bash
GET /api/user-specific-pricing-import/data
```

**Response:**

```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 123,
                "email": "john.doe@example.com",
                "first_name": "John",
                "last_name": "Doe"
            }
        ],
        "variants": [
            {
                "id": 456,
                "sku": "PROD-001",
                "product": {
                    "id": 789,
                    "name": "Product Name"
                }
            }
        ],
        "methods": ["seller_1st", "seller_next", "tiktok_1st", "tiktok_next"]
    }
}
```

## 🚀 **Testing với cURL**

### **Test lấy giá riêng:**

```bash
curl -X GET "http://your-domain.com/api/user-specific-pricing/123/456/seller_1st" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### **Test tạo giá riêng:**

```bash
curl -X POST "http://your-domain.com/api/user-specific-pricing" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "user_id": 123,
    "variant_id": 456,
    "method": "seller_1st",
    "price": 15.99,
    "currency": "USD"
  }'
```

### **Test import CSV:**

```bash
curl -X POST "http://your-domain.com/api/user-specific-pricing-import/csv" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -F "csv_file=@prices.csv"
```
