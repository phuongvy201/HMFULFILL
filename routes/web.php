<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderUploadController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierFulfillmentController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;

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
Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {

    // Main admin dashboard route (alias for statistics dashboard)
    Route::get('/', [App\Http\Controllers\Admin\OrderStatisticsController::class, 'dashboard'])->name('admin.dashboard');

    Route::view('/products', 'admin.products.product-list')->name('admin.products');

    Route::get('/categories/data', [CategoryController::class, 'index'])->name('admin.categories.data');
    Route::get('/categories', [CategoryController::class, 'showCategories'])->name('admin.categories');
    Route::get('/add-category', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/add-category', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/update/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');

    Route::get('/customers', [AdminController::class, 'customerList'])->name('admin.customers.index');
    Route::get('/customers/{id}', [AdminController::class, 'customerShow'])->name('admin.customers.show');
    Route::get('/customers/{id}/edit', [AdminController::class, 'customerEdit'])->name('admin.customers.edit');
    Route::put('/customers/{id}', [AdminController::class, 'customerUpdate'])->name('admin.customers.update');
    Route::delete('/customers/{id}', [AdminController::class, 'customerDestroy'])->name('admin.customers.destroy');

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

    // Topup routes
    Route::get('/topup-requests', [FinanceController::class, 'topupRequests'])->name('admin.topup.requests');
    Route::get('/topup/approve/{id}', [FinanceController::class, 'approveTopup'])->name('admin.topup.approve');
    Route::get('/topup/reject/{id}', [FinanceController::class, 'rejectTopup'])->name('admin.topup.reject');

    // Finance routes
    Route::get('/finance/balance-overview', [FinanceController::class, 'balanceOverview'])->name('admin.finance.balance-overview');
    Route::get('/finance/user-balance/{userId}', [FinanceController::class, 'userBalance'])->name('admin.finance.user-balance');
    Route::post('/finance/adjust-balance/{userId}', [FinanceController::class, 'adjustBalance'])->name('admin.finance.adjust-balance');
    Route::get('/finance/refundable-transactions', [FinanceController::class, 'refundableTransactions'])->name('admin.finance.refundable-transactions');
    Route::post('/finance/refund-transaction/{transactionId}', [FinanceController::class, 'refundTransaction'])->name('admin.finance.refund-transaction');

    Route::post('/admin/import-tracking', [SupplierFulfillmentController::class, 'importTrackingNumbers'])->name('admin.import-tracking');
    Route::post('/fulfillment/files/{id}/update-status', [SupplierFulfillmentController::class, 'updateStatus'])->name('fulfillment.files.update-status');
    Route::post('/orders/{orderId}/cancel', [SupplierFulfillmentController::class, 'cancelOrder'])->name('admin.orders.cancel');
    Route::put('/dtf/orders/{orderId}', [OrderUploadController::class, 'updateDtfOrder'])
        ->name('api.dtf.orders.update');

    // User Tier routes
    Route::get('/user-tiers', [App\Http\Controllers\Admin\UserTierController::class, 'index'])->name('admin.user-tiers.index');
    Route::get('/user-tiers/{user}', [App\Http\Controllers\Admin\UserTierController::class, 'show'])->name('admin.user-tiers.show');
    Route::post('/user-tiers/calculate', [App\Http\Controllers\Admin\UserTierController::class, 'calculateTiers'])->name('admin.user-tiers.calculate');
    Route::post('/user-tiers/{user}/calculate', [App\Http\Controllers\Admin\UserTierController::class, 'calculateTierForUser'])->name('admin.user-tiers.calculate-user');
    Route::get('/user-tiers/statistics', [App\Http\Controllers\Admin\UserTierController::class, 'getStatistics'])->name('admin.user-tiers.statistics');
    Route::put('/user-tiers/{user}', [App\Http\Controllers\Admin\UserTierController::class, 'updateTier'])->name('admin.user-tiers.update');

    // Statistics routes
    Route::get('/dashboard', [App\Http\Controllers\Admin\OrderStatisticsController::class, 'dashboard'])->name('admin.statistics.dashboard');
    Route::get('/statistics/detailed', [App\Http\Controllers\Admin\OrderStatisticsController::class, 'detailedStats'])->name('admin.statistics.detailed');
    Route::get('/statistics/reports', [App\Http\Controllers\Admin\OrderStatisticsController::class, 'reports'])->name('admin.statistics.reports');

    // Topup Statistics routes
    Route::get('/statistics/topup', [App\Http\Controllers\Admin\TopupStatisticsController::class, 'dashboard'])->name('admin.statistics.topup-dashboard');

    // Tier Statistics routes
    Route::get('/statistics/tier', [App\Http\Controllers\Admin\TierStatisticsController::class, 'dashboard'])->name('admin.statistics.tier-dashboard');

    // Route cho admin đổi status
    Route::post('/admin/orders/change-status/{id}', [SupplierFulfillmentController::class, 'changeStatus'])
        ->middleware(['auth', 'admin'])
        ->name('admin.orders.change-status');

    Route::get('/api-orders', [SupplierFulfillmentController::class, 'getAdminApiOrders'])
        ->name('admin.api-orders')
        ->middleware(['auth', 'admin']);

    Route::get('/all-orders', [SupplierFulfillmentController::class, 'getAdminAllOrders'])
        ->name('admin.all-orders')
        ->middleware(['auth', 'admin']);

    // Route để đẩy đơn hàng qua xưởng
    Route::post('/api-orders/process', [SupplierFulfillmentController::class, 'processOrdersToFactory'])
        ->name('admin.api-orders.process')
        ->middleware(['auth', 'admin']);

    // Export Orders CSV
    Route::get('/orders/export-csv', function () {
        return view('admin.orders.export-csv');
    })->name('admin.orders.export-csv.form')->middleware(['auth', 'admin']);

    Route::post('/orders/export-csv', [SupplierFulfillmentController::class, 'exportOrdersCSV'])
        ->name('admin.orders.export-csv')
        ->middleware(['auth', 'admin']);

    // DTF Orders routes
    Route::put('/api/dtf/orders/{orderId}', [OrderUploadController::class, 'updateDtfOrder'])
        ->name('dtf.orders.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    // API Orders routes
    Route::get('/admin/api-orders', [SupplierFulfillmentController::class, 'getAdminApiOrders'])
        ->name('admin.api-orders');
    Route::get('/admin/orders/{order}', [SupplierFulfillmentController::class, 'showOrder'])
        ->name('admin.orders.show');
});

Route::prefix('customer')->middleware('auth')->group(function () {
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('customer.index');
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    Route::get('/wallet', [FinanceController::class, 'index'])->name('customer.wallet');
    Route::post('/wallet/topup', [FinanceController::class, 'topup'])->name('customer.finance.topup');

    // Tier routes
    Route::get('/tier', [App\Http\Controllers\Customer\TierController::class, 'index'])->name('customer.tier');
    Route::get('/tier/api', [App\Http\Controllers\Customer\TierController::class, 'apiGetTierInfo'])->name('customer.tier.api');

    Route::post('/order-upload', [SupplierFulfillmentController::class, 'uploadCustomerFulfillmentFile'])->name('customer.order-upload');
    Route::get('/order-list', [SupplierFulfillmentController::class, 'getCustomerUploadedFiles'])->name('customer.order-list');
    Route::post('/delete-files', [SupplierFulfillmentController::class, 'deleteFiles'])->name('customer.delete-files');

    Route::get('/orders/{externalId}', [SupplierFulfillmentController::class, 'getCustomerOrderDetail'])
        ->where('externalId', '[A-Za-z0-9\-_]+')
        ->name('customer.orders.detail');

    Route::get('/order-customer', [SupplierFulfillmentController::class, 'getCustomerOrders'])->name('customer.order-customer');
    Route::get('/order-create', [SupplierFulfillmentController::class, 'orderCreate'])->name('customer.order-create');
    Route::get('/file-detail/{id}', [SupplierFulfillmentController::class, 'fileDetail'])->name('customer.file-detail');
    Route::get('/debug-orders', [SupplierFulfillmentController::class, 'debugCustomerOrders'])->name('customer.debug-orders');

    // Customer Export Orders
    Route::post('/orders/export', [SupplierFulfillmentController::class, 'exportCustomerOrdersCSV'])->name('customer.orders.export');
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
Route::get('/test-batch-order', [OrderUploadController::class, 'testBatchOrder']);
Route::post('/find-variant-sku/{productId}', [ProductController::class, 'findVariantSku'])->name('products.find-variant-sku');
Route::get('/products/{slug}', [ProductController::class, 'productList']);
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

// Admin Topup Routes

Route::get('login', function () {
    return redirect()->route('signin');
})->name('login');

// API Token routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/api-token', [App\Http\Controllers\ApiTokenController::class, 'show'])->name('api-token.show');
    Route::post('/profile/api-token/regenerate', [App\Http\Controllers\ApiTokenController::class, 'regenerate'])->name('api-token.regenerate');
});
