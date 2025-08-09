# 🚀 Cách truy cập User Pricing Import

## 📍 Đường dẫn truy cập

### **1. Qua Menu Admin:**

1. Đăng nhập vào admin panel
2. Vào menu **Products** (bên trái)
3. Click **User Pricing Import**

### **2. Truy cập trực tiếp:**

```
/admin/user-pricing/import
```

## 🔧 Các tính năng có sẵn

### **📥 Import User Pricing:**

-   Upload file Excel với cấu trúc:
    -   A: User ID
    -   B: Product Name
    -   C: Variant SKU
    -   D: Method (tiktok_1st/tiktok_next/seller_1st/seller_next)
    -   E: Price
    -   F: Currency

### **📄 Download Template:**

-   Click "Download Template" để tải file Excel mẫu
-   File mẫu có sẵn dữ liệu ví dụ

### **📋 Hướng dẫn chi tiết:**

-   Bảng cấu trúc file Excel
-   Ví dụ dữ liệu
-   Lưu ý validation

## 🎯 Cấu trúc Database

### **Bảng `shipping_prices`:**

-   Lưu giá **mặc định** (Wood tier)
-   Import từ cột T-W trong file sản phẩm

### **Bảng `shipping_overrides`:**

-   Lưu giá **override** cho tier và user
-   Import từ cột X-AM (Silver, Gold, Diamond, Special)
-   Import user pricing riêng biệt

## 📊 Logic ưu tiên giá

1. **User-specific price** (cao nhất)
2. **Tier-specific price** (Silver, Gold, Diamond, Special)
3. **Base price** (Wood tier) - thấp nhất

## 🔗 Routes đã tạo

```php
// User Pricing routes
Route::get('/user-pricing/import', [UserPricingController::class, 'showImportForm'])
    ->name('admin.user-pricing.import');
Route::post('/user-pricing/import', [UserPricingController::class, 'import'])
    ->name('admin.user-pricing.import');
Route::get('/user-pricing/template', [UserPricingController::class, 'exportTemplate'])
    ->name('admin.user-pricing.template');
```

## 📁 Files liên quan

1. **Controller:** `app/Http/Controllers/UserPricingController.php`
2. **View:** `resources/views/admin/user-pricing/import.blade.php`
3. **Routes:** `routes/web.php`
4. **Menu:** `resources/views/partials/admin/sidebar-menus.blade.php`

## ⚠️ Lưu ý

-   Cần đăng nhập với quyền admin
-   Import sản phẩm trước khi import user pricing
-   User ID và Variant SKU phải tồn tại trong database
-   Method phải là một trong 4 giá trị cho phép
