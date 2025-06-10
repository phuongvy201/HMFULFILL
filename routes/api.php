<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierFulfillmentController;
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
