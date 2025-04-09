<?php

use App\Http\Controllers\OrderUploadController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierFulfillmentController;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::view('/', 'customer.home');
Route::view('/home', 'customer.home');

// Nhóm các trang tĩnh của khách hàng
Route::prefix('pages')->group(function () {
    Route::view('/contact-us', 'customer.pages.contact-us');
    Route::view('/catalog-uk', 'customer.pages.catalog-uk');
    Route::view('/catalog-us', 'customer.pages.catalog-us');
    Route::view('/catalog-vn', 'customer.pages.catalog-vn');
    Route::view('/payment-policy', 'customer.pages.payment-policy');
    Route::view('/privacy-policy', 'customer.pages.privacy-policy');
    Route::view('/return-refund-policy', 'customer.pages.return-refund-policy');
    Route::view('/shipping-policy', 'customer.pages.shipping-policy');
    Route::view('/term-condition', 'customer.pages.term-condition');
    Route::view('/help-center', 'customer.pages.help-center');
});

// Nhóm route liên quan đến sản phẩm
Route::prefix('products')->group(function () {
    Route::view('/', 'customer.products.products');
    Route::get('/{slug}', function ($slug) {
        return view('customer.products.product-detail', compact('slug'));
    });
});

// Nhóm route admin
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::view('/products', 'admin.products.product-list')->name('admin.products');
    Route::get('/add-product', [ProductController::class, 'create'])->name('admin.products.create');

    Route::get('/categories/data', [CategoryController::class, 'index'])->name('admin.categories.data');
    Route::get('/categories', [CategoryController::class, 'showCategories'])->name('admin.categories');
    Route::get('/add-category', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/add-category', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/update/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');




    Route::post('/fulfillment/upload', [SupplierFulfillmentController::class, 'uploadFulfillmentFile'])->name('fulfillment.upload');
    Route::get('/fulfillment', [SupplierFulfillmentController::class, 'index'])->name('fulfillment.index');
    Route::get('/orders/import-file-fulfillment', [SupplierFulfillmentController::class, 'uploadFulfillmentFile'])->name('admin.orders.import-file-fulfillment');
    Route::get('/order-fulfillment-list', [SupplierFulfillmentController::class, 'orderFulfillmentList'])->name('admin.order-fulfillment-list');
    Route::delete('/fulfillment/files/destroy', [SupplierFulfillmentController::class, 'destroy'])->name('fulfillment.files.destroy');
    Route::get('/order-fulfillment-detail/{id}', [SupplierFulfillmentController::class, 'orderFulfillmentDetail'])->name('admin.order-fulfillment-detail');
    Route::post('/order-fulfillment-upload', [OrderUploadController::class, 'upload'])->name('admin.order-fulfillment-upload');
    Route::delete('/order-fulfillment/{id}', [OrderUploadController::class, 'destroy'])->name('admin.order-fulfillment.destroy');
    Route::delete('/order-fulfillment/bulk-delete', [OrderUploadController::class, 'destroyMultiple'])->name('admin.order-fulfillment.bulk-destroy');
    Route::get('/submitted-orders', [OrderUploadController::class, 'index'])->name('admin.submitted-orders');
});

// Nhóm route authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('register', 'showRegistrationForm')->name('register');
    Route::post('register', 'register');
    Route::get('verify-email/{token}', 'verifyEmail');
    Route::get('verification-code-form', 'showVerificationCodeForm')->name('verification.code.form');
    Route::post('verify-code', 'verifyCode')->name('verify.code');
});

// Trang fulfill (cần đăng nhập)
Route::view('fulfill', 'fulfill')->middleware('auth');

Route::get('/test-order', [OrderUploadController::class, 'testOrder']);
