<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\AddressApiController;
use App\Http\Controllers\Api\WalletApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\NotificationApiController;

use App\Http\Controllers\Api\AdminDashboardApiController;
use App\Http\Controllers\Api\UserApiController;

// ==========================================
// PUBLIC ROUTES (Không cần đăng nhập)
// ==========================================

// Auth
Route::post('/register', [AuthApiController::class, 'signup']);
Route::post('/verify-otp', [AuthApiController::class, 'verifyOtp']);
Route::post('/login', [AuthApiController::class, 'login']);

Route::post('/forgot-password', [AuthApiController::class, 'createCodeResetPassword']);
Route::post('/check-reset-code', [AuthApiController::class, 'checkCodeResetPassword']);
Route::post('/reset-password', [AuthApiController::class, 'resetPassword']);

// resend otp nên để public
Route::middleware('throttle:3,1')->group(function () {
    Route::post('/resend-otp', [AuthApiController::class, 'resendOtp']);
});

// Products
Route::get('/get_list_products', [ProductApiController::class, 'index']);
Route::get('/get_list_categories', [ProductApiController::class, 'getCategories']);
Route::get('/get_list_brands', [ProductApiController::class, 'getBrands']);
Route::get('/product_detail/{id}', [ProductApiController::class, 'show']);
Route::get('/search_products', [ProductApiController::class, 'search']);

// News
Route::get('/get_list_news', [NewsApiController::class, 'index']);
Route::get('/news_detail/{id}', [NewsApiController::class, 'show']);


// ==========================================
// USER ROUTES (CẦN ĐĂNG NHẬP)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/change-password', [AuthApiController::class, 'changePassword']);

    // Profile
    Route::get('/profile', [ProfileApiController::class, 'me']);
    Route::post('/profile/update', [ProfileApiController::class, 'update']);

    
    // Address
    Route::get('/addresses', [AddressApiController::class, 'index']);
    Route::post('/addresses', [AddressApiController::class, 'store']);
    Route::put('/addresses/{id}', [AddressApiController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressApiController::class, 'destroy']);

    Route::post('/shipping-fee', [AddressApiController::class, 'getShipFee']);

    // Wallet
    Route::get('/wallet/balance', [WalletApiController::class, 'balance']);
    Route::get('/wallet/history', [WalletApiController::class, 'history']);
    Route::post('/wallet/deposit', [WalletApiController::class, 'deposit']);

    // nếu controller có method confirmDeposit
    Route::post('/wallet/confirm-deposit', [WalletApiController::class, 'confirmDeposit']);

    // Cart
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'add']);

    Route::put('/cart/update/{id}', [CartApiController::class, 'update']);

    Route::delete('/cart/remove/{id}', [CartApiController::class, 'destroy']);

    Route::post('/cart/clear', [CartApiController::class, 'clear']);

    // Orders
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);

    Route::post('/orders/place', [OrderApiController::class, 'store']);

    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'cancel']);

    Route::post('/orders/{id}/refund', [OrderApiController::class, 'requestRefund']);

    // nếu controller có
    Route::post('/orders/{id}/confirm-received', [OrderApiController::class, 'confirmReceived']);

    Route::get('/orders/{id}/timeline', [OrderApiController::class, 'getTimeline']);

    Route::get('/orders/{id}/status', [OrderApiController::class, 'getStatus']);

    Route::post('/orders/{id}/edit-note', [OrderApiController::class, 'editNote']);

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);

    Route::post('/notifications/read', [NotificationApiController::class, 'markAsRead']);

    });


// ==========================================
// ADMIN ROUTES (CẦN QUYỀN ADMIN)
// ==========================================
Route::middleware(['auth:sanctum', 'check.admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [AdminDashboardApiController::class, 'index']);

    // Users
    Route::get('/admin/users', [UserApiController::class, 'index']);
    Route::get('/admin/users/{id}', [UserApiController::class, 'show']);
    Route::post('/admin/users', [UserApiController::class, 'store']);
    Route::put('/admin/users/{id}', [UserApiController::class, 'update']);
    Route::delete('/admin/users/{id}', [UserApiController::class, 'destroy']);

    // Products
    Route::post('/add_products', [ProductApiController::class, 'store']);

    Route::put('/edit_products/{id}', [ProductApiController::class, 'update']);

    Route::delete('/del_products/{id}', [ProductApiController::class, 'destroy']);

    // Orders
    Route::put('/admin_update_order_status/{id}', [OrderApiController::class, 'adminUpdateStatus']);

    // refund approve nếu controller có
    Route::post('/admin/orders/{id}/approve-refund', [OrderApiController::class, 'adminApproveRefund']);

    // TODO:
    // Route::get('/admin/refunds', ...);
    // Route::get('/admin/transactions', ...);
});