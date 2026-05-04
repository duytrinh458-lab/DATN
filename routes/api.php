<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CartApiController;

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