<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

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
});

// Trang quên mật khẩu
Route::get('/forgot', function () {
    return view('forgot');
});


// ================= REGISTER OTP =================

// 🔥 PHẢI TRÙNG VỚI BLADE
Route::post('/send-otp-register', [AuthController::class, 'sendOtpRegister']);
Route::post('/verify-otp-register', [AuthController::class, 'verifyOtpRegister']);


// ================= LOGIN =================
Route::post('/login', [AuthController::class, 'login']);


// ================= FORGOT PASSWORD =================
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtpForgotPassword']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtpForgotPassword']);


// Fix database
Route::get('/fix-db', [AuthController::class, 'fixDatabase']);


// Trang chủ
Route::get('/home', [HomeController::class, 'index'])->name('home');