# Hướng dẫn lấy và xử lý image từ bảng ProductImage

## Cấu trúc bảng ProductImage

Bảng `ProductImage` có cấu trúc như sau:

-   `id`: Primary key
-   `product_id`: Foreign key liên kết với bảng `products`
-   `image_url`: URL hoặc đường dẫn tới hình ảnh
-   `created_at`: Thời gian tạo
-   `updated_at`: Thời gian cập nhật

## Mối quan hệ Model

### Product Model

```php
public function images()
{
    return $this->hasMany(ProductImage::class);
}
```

### ProductImage Model

```php
public function product()
{
    return $this->belongsTo(Product::class);
}
```

## Các phương thức có sẵn

### 1. getCustomerProductsWithVariants()

**Mục đích**: Lấy danh sách sản phẩm với variants và hình ảnh cho khách hàng

**Endpoint**: `GET /customer/api/customer-products-with-variants`

**Response**:

```json
[
    {
        "id": 1,
        "name": "Premium T-Shirt",
        "description": "High quality cotton t-shirt",
        "base_price": 15.99,
        "currency": "GBP",
        "category": "T-Shirts",
        "image_url": "https://domain.com/images/product1.jpg",
        "images": [
            {
                "id": 1,
                "url": "https://domain.com/images/product1.jpg",
                "created_at": "2025-01-15T10:00:00Z"
            }
        ],
        "image_count": 1,
        "variants": [...]
    }
]
```

### 2. getProductsWithImages()

**Mục đích**: Lấy danh sách sản phẩm với hình ảnh và phân trang

**Endpoint**: `GET /customer/api/products-with-images`

**Parameters**:

-   `per_page`: Số sản phẩm mỗi trang (mặc định: 10)
-   `search`: Tìm kiếm theo tên hoặc mô tả sản phẩm

**Response**:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Premium T-Shirt",
            "description": "High quality cotton t-shirt",
            "category": "T-Shirts",
            "main_image": {
                "id": 1,
                "url": "https://domain.com/images/product1.jpg",
                "created_at": "2025-01-15T10:00:00Z"
            },
            "all_images": [...],
            "image_count": 3
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 50,
        "last_page": 5
    }
}
```

### 3. getProductImages()

**Mục đích**: Lấy tất cả hình ảnh của một sản phẩm cụ thể

**Endpoint**: `GET /customer/api/product/{productId}/images`

**Response**:

```json
{
    "success": true,
    "data": {
        "product_id": 1,
        "product_name": "Premium T-Shirt",
        "main_image": {
            "id": 1,
            "url": "https://domain.com/images/product1.jpg",
            "created_at": "2025-01-15T10:00:00Z"
        },
        "all_images": [
            {
                "id": 1,
                "url": "https://domain.com/images/product1.jpg",
                "created_at": "2025-01-15T10:00:00Z"
            },
            {
                "id": 2,
                "url": "https://domain.com/images/product1-back.jpg",
                "created_at": "2025-01-15T10:05:00Z"
            }
        ],
        "image_count": 2
    }
}
```

### 4. addProductImage() (Admin only)

**Mục đích**: Thêm hình ảnh mới cho sản phẩm

**Endpoint**: `POST /admin/products/{productId}/images`

**Request Body**:

```json
{
    "image_url": "https://domain.com/images/new-product-image.jpg"
}
```

**Response**:

```json
{
    "success": true,
    "message": "Thêm hình ảnh thành công",
    "data": {
        "id": 3,
        "product_id": 1,
        "url": "https://domain.com/images/new-product-image.jpg",
        "created_at": "2025-01-15T11:00:00Z"
    }
}
```

## Các helper methods

### formatImageUrl()

Đảm bảo URL hình ảnh là đường dẫn đầy đủ:

-   Nếu đã là URL đầy đủ (bắt đầu bằng http) → trả về nguyên
-   Nếu là đường dẫn tương đối → thêm asset()

### getMainProductImage()

Lấy hình ảnh chính (ảnh đầu tiên) của sản phẩm

### getAllProductImages()

Lấy tất cả hình ảnh của sản phẩm

## Ví dụ sử dụng trong code

### 1. Lấy sản phẩm với hình ảnh

```php
$product = Product::with('images')->find(1);

// Lấy ảnh chính
$mainImage = $product->images->first();

// Lấy tất cả ảnh
$allImages = $product->images;

// Kiểm tra có ảnh không
if ($product->images->isNotEmpty()) {
    // Có ảnh
    echo $product->images->count() . " ảnh";
}
```

### 2. Thêm ảnh cho sản phẩm

```php
$productImage = ProductImage::create([
    'product_id' => 1,
    'image_url' => 'https://domain.com/image.jpg'
]);
```

### 3. Query với images

```php
$products = Product::with([
    'images' => function ($query) {
        $query->orderBy('created_at', 'asc');
    }
])->get();
```

## Lưu ý quan trọng

1. **Performance**: Luôn sử dụng `with()` để eager load images tránh N+1 problem
2. **URL format**: Hệ thống tự động format URL đảm bảo tương thích
3. **Order**: Images được sắp xếp theo thời gian tạo (ảnh đầu tiên là ảnh chính)
4. **Validation**: URL phải hợp lệ khi thêm ảnh mới
5. **Authorization**: Chỉ admin mới được thêm/sửa/xóa ảnh

## Cách test các API

### Test với curl

```bash
# Lấy sản phẩm với images
curl -X GET "http://localhost/customer/api/products-with-images?per_page=5&search=shirt"

# Lấy images của sản phẩm cụ thể
curl -X GET "http://localhost/customer/api/product/1/images"

# Thêm image cho sản phẩm (cần auth admin)
curl -X POST "http://localhost/admin/products/1/images" \
  -H "Content-Type: application/json" \
  -d '{"image_url": "https://example.com/image.jpg"}'
```

## Troubleshooting

### Lỗi thường gặp:

1. **Image không hiển thị**: Kiểm tra URL có đúng format không
2. **N+1 query**: Đảm bảo sử dụng `with('images')`
3. **Permission denied**: Kiểm tra middleware auth và admin
4. **Invalid URL**: URL phải bắt đầu bằng http hoặc https
