<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;

// Trang mặc định
Route::get('/', function () {
    return view('welcome');
});

// ================= AUTH VIEW =================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister']);
Route::get('/forgot', [AuthController::class, 'showForgot']);


// ================= REGISTER OTP =================
Route::post('/send-otp-register', [AuthController::class, 'sendOtpRegister']);
Route::post('/verify-otp-register', [AuthController::class, 'verifyOtpRegister']);


// ================= LOGIN =================
Route::post('/login', [AuthController::class, 'login']);


// ================= FORGOT PASSWORD =================
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtpForgotPassword']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtpForgotPassword']);


// ================= HOME =================
Route::get('/home', [HomeController::class, 'index'])
    ->middleware(Authenticate::class)
    ->name('home');


// ================= PRODUCTS (🔥 FIX 404 Ở ĐÂY) =================
Route::get('/products', [ProductController::class, 'products']);


// ================= ADMIN =================
Route::prefix('admin')
    ->middleware([Authenticate::class, AdminMiddleware::class])
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard']);
        Route::resource('products', ProductController::class);
    });