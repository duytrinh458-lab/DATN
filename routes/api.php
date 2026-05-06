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

// ==========================================================
// 1. PUBLIC API (Không cần đăng nhập)
// ==========================================================

// Chuẩn RESTful của nhóm
Route::post('/auth/signup', [AuthApiController::class, 'signup']);
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);

// ALIAS (Bí danh) cho Thầy
Route::post('/signup', [AuthApiController::class, 'signup']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::get('/get_list_products', [ProductApiController::class, 'index']);
Route::get('/get_products', [ProductApiController::class, 'index']);
Route::get('/search', [ProductApiController::class, 'search']);

// ==========================================================
// 2. PRIVATE API (Bắt buộc đăng nhập - Có Token)
// ==========================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- AUTH & PROFILE ---
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/profile', [ProfileApiController::class, 'me']);
    Route::put('/profile', [ProfileApiController::class, 'update']);

    Route::post('/logout', [AuthApiController::class, 'logout']); // Alias
    Route::get('/get_user_info', [ProfileApiController::class, 'me']); // Alias
    Route::post('/set_user_info', [ProfileApiController::class, 'update']); // Alias
    Route::post('/change_password', [ProfileApiController::class, 'changePassword']); // Alias

    // --- GIỎ HÀNG (CART) ---
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'add']);
    
    Route::post('/add_to_cart', [CartApiController::class, 'add']); // Alias

    // --- ĐỊA CHỈ (ADDRESS) ---
    Route::get('/addresses', [AddressApiController::class, 'index']);
    Route::post('/addresses', [AddressApiController::class, 'store']);
    
    Route::get('/get_list_order_address', [AddressApiController::class, 'index']); // Alias
    Route::post('/add_order_address', [AddressApiController::class, 'store']); // Alias

    // --- VÍ V-PAY (WALLET) ---
    Route::get('/wallet/balance', [WalletApiController::class, 'balance']);
    Route::post('/wallet/deposit', [WalletApiController::class, 'deposit']);
    
    Route::get('/get_current_balance', [WalletApiController::class, 'balance']); // Alias

    // --- LỆNH ĐIỀU ĐỘNG (ORDERS) ---
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::post('/orders', [OrderApiController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'cancel']);

    Route::get('/get_list_purchases', [OrderApiController::class, 'index']); // Alias
    Route::post('/create_order', [OrderApiController::class, 'store']); // Alias
    Route::post('/cancel_order', [OrderApiController::class, 'cancel']); // Alias
});