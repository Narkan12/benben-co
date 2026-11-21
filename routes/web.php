<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\PublicStoreController;
use App\Http\Controllers\StorageImageController;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\CustomerOnly;
use Illuminate\Support\Facades\Route;

Route::get('/media/{path}', [StorageImageController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/', [PublicStoreController::class, 'home'])->name('home');
Route::get('/about', [PublicStoreController::class, 'about'])->name('about');
Route::get('/products', [PublicStoreController::class, 'products'])->name('products');
Route::get('/products/{slug}', [PublicStoreController::class, 'productDetail'])->name('product.detail');
Route::post('/products/{product}/review', [OrderController::class, 'submitReview'])->name('product.review');
Route::get('/promotions', [PublicStoreController::class, 'promos'])->name('promotions');
Route::get('/contact', [PublicStoreController::class, 'contact'])->name('contact');

Route::middleware([CustomerOnly::class, 'prevent-back-cache'])->group(function () {
    Route::get('/cart', [PublicStoreController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/{productId}/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{productId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/promo-preview', [CheckoutController::class, 'previewPromotion'])->name('checkout.promo-preview');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{code?}', [PublicStoreController::class, 'success'])->name('checkout.success');
});

Route::get('/login', [AuthController::class, 'showCustomerLogin'])->name('login');
Route::post('/login', [AuthController::class, 'customerLogin'])->middleware('throttle:5,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', fn () => view('auth.register'))->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::get('/auth/google/{intent}', [GoogleController::class, 'redirect'])
    ->whereIn('intent', ['customer', 'admin'])
    ->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->name('google.callback');

Route::prefix('customer')->name('customer.')->middleware([CustomerOnly::class, 'prevent-back-cache'])->group(function () {
    Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [OrderController::class, 'profile'])->name('profile');
    Route::get('/security', [OrderController::class, 'security'])->name('security');
    Route::patch('/profile', [OrderController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('order.detail');
    Route::patch('/orders/{id}/payment-proof', [OrderController::class, 'submitPaymentProof'])->name('order.payment-proof');
    Route::patch('/orders/{id}/confirm-received', [OrderController::class, 'confirmReceived'])->name('order.confirm-received');
    Route::get('/orders/{id}/proof', [OrderController::class, 'proof'])->name('order.proof');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware([AdminOnly::class, 'prevent-back-cache'])->group(function () {
        Route::get('/payment-proofs/{path}', [StorageImageController::class, 'paymentProof'])
            ->where('path', '.*')
            ->name('payments.proof');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/order-notifications/toggle', [AdminController::class, 'toggleOrderNotifications'])->name('order-notifications.toggle');
        Route::get('/order-notifications', [AdminController::class, 'orderNotifications'])->name('order-notifications');
        Route::post('/order-notifications/seen', [AdminController::class, 'markOrderNotificationsSeen'])->name('order-notifications.seen');
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
        Route::get('/pos/{order}/receipt', [POSController::class, 'receipt'])->name('pos.receipt');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/company-profile', [AdminController::class, 'companyProfile'])->name('company-profile');
        Route::put('/company-profile/images/{imageKey}', [AdminController::class, 'updateCompanyProfileImage'])->name('company-profile.images.update');
        Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('/pages/{pageKey}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{pageKey}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::get('/pages/{pageKey}/preview', [AdminPageController::class, 'preview'])->name('pages.preview');
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::post('/products', [AdminController::class, 'saveProduct'])->name('products.store');
        Route::put('/products/{productId}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{productId}', [AdminController::class, 'deleteProduct'])->name('products.delete');
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'saveCategory'])->name('categories.store');
        Route::put('/categories/{categoryId}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{categoryId}', [AdminController::class, 'deleteCategory'])->name('categories.delete');
        Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');
        Route::patch('/inventory/{productId}', [AdminController::class, 'updateStock'])->name('inventory.update');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::patch('/orders/{orderId}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status');
        Route::patch('/orders/{orderId}/receive-cash', [AdminController::class, 'markCashPaymentReceived'])->name('orders.receive-cash');
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
        Route::patch('/payments/{orderId}/verify', [AdminController::class, 'verifyPayment'])->name('payments.verify');
        Route::patch('/payments/{orderId}/reject', [AdminController::class, 'rejectPayment'])->name('payments.reject');
        Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
        Route::get('/ratings', [AdminController::class, 'ratings'])->name('ratings');
        Route::get('/promotions', [AdminController::class, 'promotions'])->name('promotions');
        Route::post('/promotions', [AdminController::class, 'savePromotion'])->name('promotions.store');
        Route::put('/promotions/{promotionId}', [AdminController::class, 'updatePromotion'])->name('promotions.update');
        Route::delete('/promotions/{promotionId}', [AdminController::class, 'deletePromotion'])->name('promotions.delete');
        Route::get('/history', [AdminController::class, 'history'])->name('history');
        Route::get('/sessions', [AdminController::class, 'sessions'])->name('sessions');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{userId}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{userId}/status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::delete('/users/{userId}', [AdminController::class, 'deleteUser'])->name('users.destroy');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/transactions', [AdminController::class, 'reportTransactions'])->name('reports.transactions');
        Route::get('/reports/sales', [AdminController::class, 'reportSales'])->name('reports.sales');
        Route::get('/reports/stock', [AdminController::class, 'reportStock'])->name('reports.stock');
        Route::get('/reports/products', [AdminController::class, 'reportProducts'])->name('reports.products');
        Route::get('/reports/finance', [AdminController::class, 'reportFinance'])->name('reports.finance');
        Route::get('/reports/export', [AdminController::class, 'exportReports'])->name('reports.export');
        Route::get('/reports/{type}/export/{format}', [AdminController::class, 'exportReport'])->name('reports.export.dynamic');
        Route::get('/reports/excel', [AdminController::class, 'exportReportsExcel'])->name('reports.excel');
    });
});
