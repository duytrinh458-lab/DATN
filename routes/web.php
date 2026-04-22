<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;

// ================= ROOT =================
Route::get('/', function () {
    return redirect('/login');
});


// ================= AUTH =================
Route::controller(AuthController::class)->group(function () {

    // VIEW
    Route::get('/login', 'showLogin')->name('login');
    Route::get('/register', 'showRegister')->name('register');
    Route::get('/forgot', 'showForgot')->name('forgot');

    // LOGIN + REGISTER
    Route::post('/login', 'login');
    Route::post('/send-otp-register', 'sendOtpRegister');
    Route::post('/verify-otp-register', 'verifyOtpRegister');

    // FORGOT PASSWORD
    Route::post('/forgot-password/send-otp', 'sendOtpForgotPassword');
    Route::post('/forgot-password/verify-otp', 'verifyOtpForgotPassword');

    // 🔥 ĐỔI MẬT KHẨU LẦN ĐẦU (WEB)
    Route::get('/change-password', 'showChangePasswordForm')->name('password.change.form');
    Route::post('/change-password', 'updatePassword')->name('password.change.update');

    // 🔥 API (nếu dùng AJAX)
    Route::post('/api/change-password-first-login', 'changePasswordFirstLogin')
        ->middleware(Authenticate::class)
        ->name('api.change-password-first-login');
});


// ================= ADMIN =================
Route::prefix('admin')
    ->name('admin.')
    ->middleware([Authenticate::class, AdminMiddleware::class])
    ->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::resource('products', ProductController::class)->except(['show']);
    });


// ================= USER =================
Route::middleware([Authenticate::class])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/products', [ProductController::class, 'products'])->name('user.products');
});