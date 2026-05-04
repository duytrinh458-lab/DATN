<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\UserApiController;

Route::apiResource('products', ProductApiController::class);
Route::apiResource('users', UserApiController::class);