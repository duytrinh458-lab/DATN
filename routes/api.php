<?php

use Illuminate\Http\Request;
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
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/verify-otp', [AuthApiController::class, 'verifyOtpRegister']);
Route::post('/login', [AuthApiController::class, 'login']);

Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthApiController::class, 'resetPassword']);

// Lấy dữ liệu công khai (Sản phẩm, Danh mục, Tin tức)
Route::get('/get_list_products', [ProductApiController::class, 'index']);
Route::get('/get_list_categories', [ProductApiController::class, 'categories']);
Route::get('/get_list_brands', [ProductApiController::class, 'brands']);
Route::get('/product_detail/{id}', [ProductApiController::class, 'show']);
Route::get('/search_products', [ProductApiController::class, 'search']);

Route::get('/get_list_news', [NewsApiController::class, 'index']);
Route::get('/news_detail/{id}', [NewsApiController::class, 'show']);

// ==========================================
// USER ROUTES (Cần đăng nhập)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // Đăng xuất & Đổi mật khẩu
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/change-password', [AuthApiController::class, 'changePassword']);

    // Hồ sơ cá nhân
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::post('/profile/update', [ProfileApiController::class, 'update']);
    Route::post('/profile/update-avatar', [ProfileApiController::class, 'updateAvatar']);
    Route::post('/profile/notification-settings', [ProfileApiController::class, 'updateNotificationSettings']);

    // Quản lý địa chỉ
    Route::get('/addresses', [AddressApiController::class, 'index']);
    Route::post('/addresses', [AddressApiController::class, 'store']);
    Route::put('/addresses/{id}', [AddressApiController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressApiController::class, 'destroy']);
    Route::post('/shipping-fee', [AddressApiController::class, 'calculateShippingFee']);

    // Ví V-Pay
    Route::get('/wallet/balance', [WalletApiController::class, 'balance']);
    Route::get('/wallet/history', [WalletApiController::class, 'history']);
    Route::post('/wallet/deposit', [WalletApiController::class, 'deposit']);

    // Giỏ hàng
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'add']);
    Route::put('/cart/update/{id}', [CartApiController::class, 'update']);
    Route::delete('/cart/remove/{id}', [CartApiController::class, 'remove']);
    Route::post('/cart/clear', [CartApiController::class, 'clear']);

    // Đơn hàng
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);
    Route::post('/orders/place', [OrderApiController::class, 'placeOrder']);
    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'cancelOrder']);
    Route::post('/orders/{id}/refund', [OrderApiController::class, 'requestRefund']);

    // Thông báo (Notifications)
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllAsRead']);
});

// ==========================================
// ADMIN ROUTES (ĐÃ VÁ LỖI: Cần check quyền Admin)
// ==========================================
Route::middleware(['auth:sanctum', 'check.admin'])->group(function () {
    // Thống kê
    Route::get('/admin/dashboard', [AdminDashboardApiController::class, 'dashboard']);

    // Quản lý User
    Route::get('/admin/users', [UserApiController::class, 'index']);
    Route::get('/admin/users/{id}', [UserApiController::class, 'show']);
    Route::post('/admin/users', [UserApiController::class, 'store']);
    Route::put('/admin/users/{id}', [UserApiController::class, 'update']);
    Route::delete('/admin/users/{id}', [UserApiController::class, 'destroy']);

    // Quản lý Sản phẩm (Thêm/Sửa/Xóa)
    Route::post('/add_products', [ProductApiController::class, 'store']);
    Route::put('/edit_products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/del_products/{id}', [ProductApiController::class, 'destroy']);

    // Quản lý Đơn hàng
    Route::put('/admin_update_order_status/{id}', [OrderApiController::class, 'updateStatus']);
});