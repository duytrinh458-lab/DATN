<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\ProfileApiController;

// ================= PRODUCT API (USER) =================
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);
Route::get('/products-search', [ProductApiController::class, 'search']);


// ================= CART API =================
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/cart', [CartApiController::class, 'index']);

    Route::post('/cart/add', [CartApiController::class, 'add']);

    // update theo product_id (không dùng id cart_item)
    Route::put('/cart/{product_id}', [CartApiController::class, 'update']);

    // delete theo product_id
    Route::delete('/cart/{product_id}', [CartApiController::class, 'destroy']);
});

// ================= USER API =================
use App\Http\Controllers\Api\UserApiController;

// Route::middleware('auth:sanctum')->group(function () {

    // lấy danh sách user
    Route::get('/users', [UserApiController::class, 'index']);

    // chi tiết user
    Route::get('/users/{id}', [UserApiController::class, 'show']);

    // tạo user
    Route::post('/users', [UserApiController::class, 'store']);

    // cập nhật user
    Route::put('/users/{id}', [UserApiController::class, 'update']);

    // xóa user
    Route::delete('/users/{id}', [UserApiController::class, 'destroy']);
// });

// ================= PROFILE API =================
Route::middleware('auth:sanctum')->group(function () {

    // lấy thông tin user đang login
    Route::get('/profile', [ProfileApiController::class, 'me']);

    // cập nhật profile
    Route::put('/profile', [ProfileApiController::class, 'update']);

    // đổi mật khẩu
    Route::put('/profile/password', [ProfileApiController::class, 'changePassword']);
});