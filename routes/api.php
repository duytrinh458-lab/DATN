<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\WalletApiController;
use App\Http\Controllers\Api\AddressApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\AdminDashboardApiController;
use App\Http\Controllers\Api\NewsApiController;

// ==========================================
// 1. PUBLIC API (Không cần đăng nhập)
// ==========================================
Route::post('/auth/signup', [AuthApiController::class, 'signup']);
Route::post('/signup', [AuthApiController::class, 'signup']); 
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/login', [AuthApiController::class, 'login']); 
Route::post('/auth/verify-otp', [AuthApiController::class, 'verifyOtp']);
Route::post('/verify_register_otp', [AuthApiController::class, 'verifyOtp']); 
Route::post('/auth/resend-otp', [AuthApiController::class, 'resendOtp']);
Route::post('/resend_otp', [AuthApiController::class, 'resendOtp']); 
Route::post('/create_code_reset_password', [AuthApiController::class, 'createCodeResetPassword']);
Route::post('/check_code_reset_password', [AuthApiController::class, 'checkCodeResetPassword']);
Route::post('/reset_password', [AuthApiController::class, 'resetPassword']);

Route::get('/get_list_products', [ProductApiController::class, 'index']);
Route::get('/get_products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);
Route::get('/search', [ProductApiController::class, 'search']);
Route::get('/check_new_items', [ProductApiController::class, 'checkNewItems']);
Route::get('/get_comments_product/{id}', [ProductApiController::class, 'getComments']);
Route::get('/get_categories', [ProductApiController::class, 'getCategories']);
Route::get('/get_list_brand', [ProductApiController::class, 'getBrands']);

Route::get('/get_list_news', [NewsApiController::class, 'index']);
Route::get('/get_news/{id}', [NewsApiController::class, 'show']);

// ==========================================
// 2. USER API (Bảo vệ bằng Middleware auth:sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/get_user_info', [ProfileApiController::class, 'me']);
    Route::post('/set_user_info', [ProfileApiController::class, 'update']);
    Route::post('/change_password', [ProfileApiController::class, 'changePassword']);
    Route::post('/set_devtoken', [ProfileApiController::class, 'setDeviceToken']);
    Route::get('/get_push_setting', [ProfileApiController::class, 'getPushSetting']);
    Route::put('/set_push_setting', [ProfileApiController::class, 'setPushSetting']);

    Route::post('/set_comments_product/{id}', [ProductApiController::class, 'setComment']);
    Route::post('/like_product/{id}', [ProductApiController::class, 'likeProduct']);
    Route::post('/save_search', [ProductApiController::class, 'saveSearch']); 
    Route::get('/get_list_search_history', [ProductApiController::class, 'getSearchHistory']);

    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/add_to_cart', [CartApiController::class, 'add']);
    Route::put('/update_cart/{id}', [CartApiController::class, 'update']);
    Route::delete('/remove_cart/{id}', [CartApiController::class, 'destroy']);
    Route::delete('/clear_cart', [CartApiController::class, 'clear']);

    Route::get('/get_list_order_address', [AddressApiController::class, 'index']); 
    Route::post('/add_order_address', [AddressApiController::class, 'store']);
    Route::put('/edit_order_address/{id}', [AddressApiController::class, 'update']);
    Route::delete('/delete_order_address/{id}', [AddressApiController::class, 'destroy']);
    Route::get('/get_ship_from', [AddressApiController::class, 'getShipFrom']);
    Route::get('/get_ship_fee', [AddressApiController::class, 'getShipFee']);

    Route::get('/get_current_balance', [WalletApiController::class, 'balance']);
    Route::get('/get_balance_history', [WalletApiController::class, 'history']);
    Route::post('/create_deposit_request', [WalletApiController::class, 'deposit']);
    Route::post('/confirm_deposit', [WalletApiController::class, 'confirmDeposit']);

    Route::get('/get_list_purchases', [OrderApiController::class, 'index']); 
    Route::get('/get_order_detail/{id}', [OrderApiController::class, 'show']);
    Route::post('/create_order', [OrderApiController::class, 'store']);
    Route::post('/cancel_order/{id}', [OrderApiController::class, 'cancel']);
    Route::get('/get_order_status/{id}', [OrderApiController::class, 'getStatus']);
    Route::get('/get_order_timeline/{id}', [OrderApiController::class, 'getTimeline']);
    Route::post('/confirm_received/{id}', [OrderApiController::class, 'confirmReceived']);
    Route::post('/request_refund/{id}', [OrderApiController::class, 'requestRefund']);
    Route::put('/edit_purchase/{id}', [OrderApiController::class, 'editNote']);

    Route::get('/get_notification', [NotificationApiController::class, 'index']);
    Route::post('/set_read_noti', [NotificationApiController::class, 'markAsRead']);
});

// ==========================================
// 3. ADMIN API (Bảo vệ bằng Token + Check AdminRole)
// ==========================================
// 💡 ĐÃ FIX LOGIC SAI: Tách riêng nhóm Admin để chặn người dùng thường gửi request xóa dữ liệu
Route::middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/admin_get_dashboard_stats', [AdminDashboardApiController::class, 'index']);
    Route::post('/add_products', [ProductApiController::class, 'store']);
    Route::put('/edit_products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/del_products/{id}', [ProductApiController::class, 'destroy']); // Đã an toàn
    
    Route::put('/admin_update_order_status/{id}', [OrderApiController::class, 'adminUpdateStatus']);
    Route::post('/admin_approve_refund/{refund_id}', [OrderApiController::class, 'adminApproveRefund']);
    
    Route::get('/get_list_users', [UserApiController::class, 'index']); // Đã an toàn
    Route::get('/get_user_detail/{id}', [UserApiController::class, 'show']); 
    Route::post('/add_user', [UserApiController::class, 'store']); 
    Route::put('/edit_user/{id}', [UserApiController::class, 'update']); 
    Route::delete('/del_user/{id}', [UserApiController::class, 'destroy']); 
});