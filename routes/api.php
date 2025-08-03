<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierFulfillmentController;
use App\Http\Controllers\OrderUploadController;
use Illuminate\Support\Facades\Route;


// Route tạo đơn hàng với middleware authentication
Route::post('/orders', [SupplierFulfillmentController::class, 'createOrder'])
    ->middleware('auth.api.token')
    ->name('api.orders.create');

Route::get('/orders/{orderId}', [SupplierFulfillmentController::class, 'getOrderDetailsApi'])
    ->middleware('auth.api.token')
    ->name('api.orders.details');

Route::put('/orders/{orderId}', [SupplierFulfillmentController::class, 'updateOrder'])
    ->middleware('auth.api.token')
    ->name('api.orders.update');

Route::put('/dtf/orders/{orderId}', [OrderUploadController::class, 'updateDtfOrder'])
    ->middleware('auth.api.token')
    ->name('api.dtf.orders.update');

Route::post('/orders/{orderId}/cancel', [SupplierFulfillmentController::class, 'cancelOrder'])
    ->middleware('auth.api.token')
    ->name('api.orders.cancel');

Route::get('/orders/search', [SupplierFulfillmentController::class, 'searchCustomerOrders'])
    ->middleware('auth.api.token')
    ->name('api.orders.search');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customer/orders/search', [SupplierFulfillmentController::class, 'searchCustomerOrders']);
});

Route::get('/products', [SupplierFulfillmentController::class, 'getProductsWithGBP'])
    ->middleware('auth.api.token')
    ->name('api.products.gbp');

// User-Specific Pricing API Routes
Route::prefix('user-specific-pricing')->middleware('auth.api.token')->group(function () {

    // Lấy giá riêng của user
    Route::get('/{userId}/{variantId}/{method}', [App\Http\Controllers\Admin\UserSpecificPricingController::class, 'getUserPrice'])
        ->name('api.user-specific-pricing.get');

    // Lấy tất cả giá riêng của user
    Route::get('/{userId}', [App\Http\Controllers\Admin\UserSpecificPricingController::class, 'getAllUserPrices'])
        ->name('api.user-specific-pricing.all');

    // Tạo giá riêng cho user
    Route::post('/', [App\Http\Controllers\Admin\UserSpecificPricingController::class, 'store'])
        ->name('api.user-specific-pricing.store');

    // Cập nhật giá riêng cho user
    Route::put('/{userId}/{variantId}/{method}', [App\Http\Controllers\Admin\UserSpecificPricingController::class, 'update'])
        ->name('api.user-specific-pricing.update');

    // Xóa giá riêng cho user
    Route::delete('/{userId}/{variantId}/{method}', [App\Http\Controllers\Admin\UserSpecificPricingController::class, 'destroy'])
        ->name('api.user-specific-pricing.destroy');

    // Copy giá từ user này sang user khác
    Route::post('/copy', [App\Http\Controllers\Admin\UserSpecificPricingController::class, 'copyPrices'])
        ->name('api.user-specific-pricing.copy');
});

// User-Specific Pricing Import API Routes
Route::prefix('user-specific-pricing-import')->middleware('auth.api.token')->group(function () {

    // Lấy dữ liệu hỗ trợ import
    Route::get('/data', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'getImportData'])
        ->name('api.user-specific-pricing-import.data');

    // Import từ CSV
    Route::post('/csv', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'import'])
        ->name('api.user-specific-pricing-import.csv');

    // Preview CSV trước khi import
    Route::post('/preview', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'preview'])
        ->name('api.user-specific-pricing-import.preview');

    // Import từ form
    Route::post('/form', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'importFromForm'])
        ->name('api.user-specific-pricing-import.form');

    // Import hàng loạt từ JSON
    Route::post('/batch', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'importBatch'])
        ->name('api.user-specific-pricing-import.batch');

    // Export giá của user
    Route::get('/export/user/{userId}', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'exportUserPrices'])
        ->name('api.user-specific-pricing-import.export-user');

    // Export tất cả giá
    Route::get('/export/all', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'exportAllPrices'])
        ->name('api.user-specific-pricing-import.export-all');

    // Download template
    Route::get('/template', [App\Http\Controllers\Admin\UserSpecificPricingImportController::class, 'downloadTemplate'])
        ->name('api.user-specific-pricing-import.template');
});
