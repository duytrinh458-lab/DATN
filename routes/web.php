<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate; // 🔥 thêm dòng này

// Trang mặc định
Route::get('/', function () {
    return view('welcome');
});

// Trang register
Route::get('/register', function () {
    return view('register');
});

// Trang login
Route::get('/login', function () {
    return view('login');
})->name('login');

// Trang quên mật khẩu
Route::get('/forgot', function () {
    return view('forgot');
});


// ================= REGISTER OTP =================
Route::post('/send-otp-register', [AuthController::class, 'sendOtpRegister']);
Route::post('/verify-otp-register', [AuthController::class, 'verifyOtpRegister']);


// ================= LOGIN =================
Route::post('/login', [AuthController::class, 'login']);


// ================= FORGOT PASSWORD =================
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtpForgotPassword']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtpForgotPassword']);


// Fix database
Route::get('/fix-db', [AuthController::class, 'fixDatabase']);


// ================= HOME (bắt buộc login) =================
Route::get('/home', [HomeController::class, 'index'])
    ->middleware(Authenticate::class)
    ->name('home');


// ================= ADMIN =================
Route::prefix('admin')
    ->middleware([Authenticate::class, AdminMiddleware::class])
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard']);
        Route::resource('products', ProductController::class);
    });