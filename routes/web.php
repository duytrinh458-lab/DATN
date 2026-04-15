<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Trang mặc định
Route::get('/', function () {
    return view('welcome');
});

// Hiển thị form đăng ký
Route::get('/register', function () {
    return view('register');
});

// Hiển thị form đăng nhập
Route::get('/login', function () {
    return view('login');
});

// Hiển thị form quên mật khẩu
Route::get('/forgot', function () {
    return view('forgot');
});

// Đăng ký bằng OTP
Route::post('/register/send-otp', [AuthController::class, 'sendOtpRegister']);
Route::post('/register/verify-otp', [AuthController::class, 'verifyOtpRegister']);

// Đăng nhập bằng email + mật khẩu
Route::post('/login', [AuthController::class, 'login']);

// Quên mật khẩu bằng OTP
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtpForgotPassword']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtpForgotPassword']);

// Đường dẫn sửa lỗi database (truy cập: http://localhost/uav-shop/public/fix-db)
Route::get('/fix-db', [AuthController::class, 'fixDatabase']);
