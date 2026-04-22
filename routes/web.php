<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;

// 1. Trang mặc định - Duy có thể sửa thành redirect sang login nếu muốn
Route::get('/', function () {
    return view('welcome');
});

// 2. ================= AUTH VIEW & LOGIC =================
// Đã khớp với folder Login (viết hoa chữ L) trong Controller của Duy
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::get('/register', 'showRegister')->name('register');
    Route::get('/forgot', 'showForgot')->name('forgot');

    Route::post('/login', 'login');
    Route::post('/send-otp-register', 'sendOtpRegister');
    Route::post('/verify-otp-register', 'verifyOtpRegister');
    Route::post('/forgot-password/send-otp', 'sendOtpForgotPassword');
    Route::post('/forgot-password/verify-otp', 'verifyOtpForgotPassword');
    
    Route::get('/change-password', 'showChangePasswordForm')->name('password.change.form');
    Route::post('/change-password', 'updatePassword')->name('password.change.update');
});

// 3. ================= ADMIN AREA =================
// Đã thêm ->name('admin.') để fix lỗi "Route not defined" tận gốc
Route::prefix('admin')
    ->name('admin.') 
    ->middleware([Authenticate::class, AdminMiddleware::class])
    ->group(function () {
        
        // Trang chủ Admin: route('admin.dashboard')
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Quản lý sản phẩm: route('admin.products.index'), route('admin.products.create')...
        Route::resource('products', ProductController::class)->except(['show']);
    });

// 4. ================= USER AREA =================
Route::middleware([Authenticate::class])->group(function () {
    
    // Trang chủ User: route('home')
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Trang danh sách sản phẩm cho User xem
    // Duy lưu ý: Đường dẫn này trỏ vào ProductController trong folder Admin theo file của Duy
    Route::get('/products', [ProductController::class, 'products'])->name('user.products');
});