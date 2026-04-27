<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\ProfileController;

// ================= ROOT =================
Route::get('/', function () {
    return redirect('/login');
});

// ================= AUTH =================
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

// ================= ADMIN =================
Route::prefix('admin')
    ->name('admin.')
    ->middleware([Authenticate::class, AdminMiddleware::class])
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('products', ProductController::class)->except(['show']);
    });

// ================= USER (Cần Đăng nhập mới mua được hàng) =================
Route::middleware([Authenticate::class])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/products', [ProductController::class, 'products'])->name('user.products');

    // ================= ORDERS =================
    Route::prefix('orders')->name('user.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index'); 
        Route::post('/checkout', [OrderController::class, 'store'])->name('store'); 
        Route::post('/confirm', [OrderController::class, 'confirm'])->name('confirm'); 
        Route::get('/{id}', [OrderController::class, 'show'])->name('show'); 
    });

    // ================= PROFILE =================
    Route::prefix('profile')->name('user.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index'); // Đường dẫn: /profile
        Route::post('/update', [ProfileController::class, 'update'])->name('update'); // Đường dẫn: /profile/update
        
        // Đã sửa lại dòng này: Bỏ /profile dư thừa ở đầu
        Route::post('/address/store', [ProfileController::class, 'storeAddress'])->name('address.store'); 
    });
});