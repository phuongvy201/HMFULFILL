<?php

use App\Http\Controllers\OrderUploadController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierFulfillmentController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::get('/', function () {
    return app()->make(ProductController::class)->index(request());
})->name('home');

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
Route::get('/products', [ProductController::class, 'productList'])->name('products.list');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');

// Nhóm route admin
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::view('/products', 'admin.products.product-list')->name('admin.products');

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
    Route::get('/customer-uploaded-files-list', [SupplierFulfillmentController::class, 'customerUploadedFilesList'])->name('admin.customer-uploaded-files-list');
    Route::delete('/fulfillment/files/destroy', [SupplierFulfillmentController::class, 'destroy'])->name('fulfillment.files.destroy');
    Route::get('/order-fulfillment-detail/{id}', [SupplierFulfillmentController::class, 'orderFulfillmentDetail'])->name('admin.order-fulfillment-detail');
    Route::post('/order-fulfillment-upload', [OrderUploadController::class, 'upload'])->name('admin.order-fulfillment-upload');
    Route::delete('/order-fulfillment/{id}', [OrderUploadController::class, 'destroy'])->name('admin.order-fulfillment.destroy');
    Route::delete('/order-fulfillment/bulk-delete', [OrderUploadController::class, 'destroyMultiple'])->name('admin.order-fulfillment.bulk-destroy');
    Route::get('/submitted-orders', [OrderUploadController::class, 'index'])->name('admin.submitted-orders');
    Route::get('/submitted-order-detail', [OrderUploadController::class, 'getOrderDetails'])->name('admin.submitted-order-detail');

    Route::get('/products', [ProductController::class, 'adminIndex'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::get('/topup-requests', [FinanceController::class, 'topupRequests'])->name('admin.topup.requests');
    Route::get('topup/approve/{id}', [FinanceController::class, 'approveTopup'])->name('admin.topup.approve');
    Route::get('topup/reject/{id}', [FinanceController::class, 'rejectTopup'])->name('admin.topup.reject');
    Route::get('finance/balance-overview', [FinanceController::class, 'balanceOverview'])->name('admin.finance.balance-overview');
    Route::get('finance/user-balance/{userId}', [FinanceController::class, 'userBalance'])->name('admin.finance.user-balance');
    Route::post('finance/adjust-balance/{userId}', [FinanceController::class, 'adjustBalance'])->name('admin.finance.adjust-balance');
    Route::get('finance/topup-requests', [FinanceController::class, 'topupRequests'])->name('admin.finance.topup-requests');
    Route::post('finance/approve-topup/{id}', [FinanceController::class, 'approveTopup'])->name('admin.finance.approve-topup');
    Route::post('finance/reject-topup/{id}', [FinanceController::class, 'rejectTopup'])->name('admin.finance.reject-topup');
});
Route::prefix('customer')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('customer.index');

    Route::get('/wallet', [FinanceController::class, 'index'])->name('customer.wallet');
    Route::post('/wallet/topup', [FinanceController::class, 'topup'])->name('customer.finance.topup');
    Route::post('/order-upload', [SupplierFulfillmentController::class, 'uploadCustomerFulfillmentFile'])->name('customer.order-upload');
    Route::get('/order-list', [SupplierFulfillmentController::class, 'getCustomerUploadedFiles'])->name('customer.order-list');
    Route::post('/delete-files', [SupplierFulfillmentController::class, 'deleteFiles'])->name('customer.delete-files');
    Route::get('/order-customer', [SupplierFulfillmentController::class, 'getCustomerOrders'])->name('customer.order-customer');
    Route::get('/orders/{id}', [SupplierFulfillmentController::class, 'getCustomerOrderDetail'])
        ->name('customer.orders.detail');
});

// Nhóm route authentication
Route::controller(RegisterController::class)->group(function () {
    Route::get('register', 'showRegistrationForm')->name('register');
    Route::post('register', 'register');
    Route::get('verify-email/{token}', 'verifyEmail');
    Route::get('verification-code-form', 'showVerificationCodeForm')->name('verification.code.form');
    Route::post('verify-code', 'verifyCode')->name('verify.code');
    Route::get('signin', 'showLoginForm')->name('signin');
    Route::post('signin', 'login');
    Route::post('logout', 'logout')->name('logout');
});

// Trang fulfill (cần đăng nhập)
Route::view('fulfill', 'fulfill')->middleware('auth');

Route::get('/test-order', [OrderUploadController::class, 'testOrder']);
Route::post('/find-variant-sku/{productId}', [ProductController::class, 'findVariantSku'])->name('products.find-variant-sku');
Route::get('/products/{slug}', [ProductController::class, 'productList']);
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

// Admin Topup Routes
