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
use App\Http\Controllers\Api\BrandApiController;

// ==========================================================
// 1. PUBLIC API (Không cần đăng nhập)
// ==========================================================

// --- NHÓM 1: AUTH ---
Route::post('/auth/signup', [AuthApiController::class, 'signup']);
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/verify-otp', [AuthApiController::class, 'verifyOtp']);
Route::post('/auth/resend-otp', [AuthApiController::class, 'resendOtp']);
Route::post('/create_code_reset_password', [AuthApiController::class, 'createCodeResetPassword']);
Route::post('/check_code_reset_password', [AuthApiController::class, 'checkCodeResetPassword']);
Route::post('/reset_password', [AuthApiController::class, 'resetPassword']);

// --- NHÓM 3: SẢN PHẨM (PUBLIC) ---
Route::get('/get_list_products', [ProductApiController::class, 'index']);
Route::get('/get_products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);
Route::get('/search', [ProductApiController::class, 'search']);
Route::get('/check_new_items', [ProductApiController::class, 'checkNewItems']);
Route::get('/get_comments_product/{id}', [ProductApiController::class, 'getComments']);
Route::get('/get_list_brand', [BrandApiController::class, 'index']);


// --- NHÓM 8: TIN TỨC (PUBLIC) ---
Route::get('/get_list_news', [NewsApiController::class, 'index']);
Route::get('/get_news/{id}', [NewsApiController::class, 'show']);


// ==========================================================
// 2. PRIVATE API (Bắt buộc đăng nhập)
// ==========================================================
// Route::middleware('auth:sanctum')->group(function () {
    
    // --- NHÓM 1 & 2: AUTH & PROFILE ---
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/get_user_info', [ProfileApiController::class, 'me']);
    Route::post('/set_user_info', [ProfileApiController::class, 'update']);
    Route::post('/change_password', [ProfileApiController::class, 'changePassword']);
    Route::post('/set_devtoken', [ProfileApiController::class, 'setDeviceToken']);
    Route::get('/get_push_setting', [ProfileApiController::class, 'getPushSetting']);
    Route::put('/set_push_setting', [ProfileApiController::class, 'setPushSetting']);

    // --- NHÓM 3: TƯƠNG TÁC SẢN PHẨM ---
    Route::post('/set_comments_product/{id}', [ProductApiController::class, 'setComment']);
    Route::post('/like_product/{id}', [ProductApiController::class, 'likeProduct']);

    // --- NHÓM 4: GIỎ HÀNG (Duy đang thiếu Update/Delete/Clear) ---
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/add_to_cart', [CartApiController::class, 'add']);
    Route::put('/update_cart/{id}', [CartApiController::class, 'update']); // Mới: Sửa số lượng
    Route::delete('/remove_cart/{id}', [CartApiController::class, 'remove']); // Mới: Xóa 1 món
    Route::delete('/clear_cart', [CartApiController::class, 'clear']); // Mới: Xóa sạch giỏ

    // --- NHÓM 5: ĐỊA CHỈ & VẬN CHUYỂN ---
    Route::get('/get_list_order_address', [AddressApiController::class, 'index']); 
    Route::post('/add_order_address', [AddressApiController::class, 'store']);
    Route::put('/edit_order_address/{id}', [AddressApiController::class, 'update']);
    Route::delete('/delete_order_address/{id}', [AddressApiController::class, 'destroy']);
    Route::get('/get_ship_from', [AddressApiController::class, 'getShipFrom']);
    Route::get('/get_ship_fee', [AddressApiController::class, 'getShipFee']);

    // --- NHÓM 7: VÍ V-PAY ---
    Route::get('/get_current_balance', [WalletApiController::class, 'balance']);
    Route::get('/get_balance_history', [WalletApiController::class, 'history']);
    Route::post('/create_deposit_request', [WalletApiController::class, 'deposit']);
    Route::post('/confirm_deposit', [WalletApiController::class, 'confirmDeposit']);

    // --- NHÓM 6: ĐƠN HÀNG (Duy đang thiếu Chi tiết/Lộ trình/Xác nhận) ---
    Route::get('/get_list_purchases', [OrderApiController::class, 'index']); 
    Route::get('/get_order_detail/{id}', [OrderApiController::class, 'show']); // Mới: Chi tiết đơn
    Route::get('/get_order_status/{id}', [OrderApiController::class, 'getStatus']); // Mới
    Route::get('/get_order_timeline/{id}', [OrderApiController::class, 'getTimeline']); // Mới
    Route::post('/confirm_received/{id}', [OrderApiController::class, 'confirmReceived']); // Mới
    Route::post('/request_refund/{id}', [OrderApiController::class, 'requestRefund']); // Mới
    Route::post('/cancel_order/{id}', [OrderApiController::class, 'cancel']);
    Route::post('/create_order', [OrderApiController::class, 'store']);


    // --- NHÓM 8: THÔNG BÁO ---
    Route::get('/get_notification', [NotificationApiController::class, 'index']); // Mới: Lấy danh sách noti
    Route::post('/set_read_noti', [NotificationApiController::class, 'markAsRead']);

    // --- NHÓM 9: QUẢN TRỊ (ADMIN) ---
    Route::get('/admin_get_dashboard_stats', [AdminDashboardApiController::class, 'index']);
    
    // Quản lý Sản phẩm
    Route::post('/add_products', [ProductApiController::class, 'store']);
    Route::put('/edit_products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/del_products/{id}', [ProductApiController::class, 'destroy']);
    
    // Quản lý Đơn hàng & Hoàn tiền
    Route::put('/admin_update_order_status/{id}', [OrderApiController::class, 'adminUpdateStatus']);
    Route::post('/admin_approve_refund/{refund_id}', [OrderApiController::class, 'adminApproveRefund']);
    
    // Quản lý Người dùng (Duy đang thiếu phần này)
    Route::get('/get_list_users', [UserApiController::class, 'index']); // Mới
    Route::get('/get_user_detail/{id}', [UserApiController::class, 'show']); // Mới
    Route::post('/add_user', [UserApiController::class, 'store']); // Mới
    Route::put('/edit_user/{id}', [UserApiController::class, 'update']); // Mới
    Route::delete('/del_user/{id}', [UserApiController::class, 'destroy']); // Mới
// });