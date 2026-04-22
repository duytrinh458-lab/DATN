<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdminController;
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



// ================= ADMIN =================
Route::prefix('admin')
->name('admin.') // 🔥 THÊM DÒNG NÀY VÀO
->middleware([Authenticate::class, AdminMiddleware::class])
->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // 🔥 FIX LỖI Ở ĐÂY (bỏ show)
    Route::resource('products', ProductController::class)->except(['show']);
    });

    // ================= User =================
    // ================= HOME =================
    Route::get('/home', [HomeController::class, 'index'])
    ->middleware(Authenticate::class)
    ->name('home');
    
    
    // ================= PRODUCTS =================
    Route::get('/products', [ProductController::class, 'products']);




