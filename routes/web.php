<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
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
Route::get('/cart', [PublicStoreController::class, 'cart'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/{productId}/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/promo-preview', [CheckoutController::class, 'previewPromotion'])->name('checkout.promo-preview');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{code?}', [PublicStoreController::class, 'success'])->name('checkout.success');
Route::get('/contact', fn () => view('contact'))->name('contact');

Route::get('/login', [AuthController::class, 'showCustomerLogin'])->name('login');
Route::post('/login', [AuthController::class, 'customerLogin'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', fn () => view('auth.register'))->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::prefix('customer')->name('customer.')->middleware(CustomerOnly::class)->group(function () {
    Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [OrderController::class, 'profile'])->name('profile');
    Route::patch('/profile', [OrderController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('order.detail');
    Route::get('/orders/{id}/proof', [OrderController::class, 'proof'])->name('order.proof');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(AdminOnly::class)->group(function () {
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
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [AdminController::class, 'exportReports'])->name('reports.export');
        Route::get('/reports/excel', [AdminController::class, 'exportReportsExcel'])->name('reports.excel');
    });
});
