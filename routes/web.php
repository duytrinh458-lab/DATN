<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;

// AUTH
use App\Http\Controllers\AuthController;

// USER CONTROLLERS
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\ProductController as UserProductController; // Để xem chi tiết SP
use App\Http\Controllers\User\CheckoutController; // Xử lý mua ngay & thanh toán

// ORDER (Dùng chung hoặc tùy cấu trúc của Duy)
use App\Http\Controllers\OrderController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController as P; // Alias P của Duy
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Middleware\AdminMiddleware;

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

        Route::resource('products', P::class)->except(['show']);

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/update', [AdminOrderController::class, 'update'])->name('orders.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/delete', [UserController::class, 'delete'])->name('users.delete');
    });

// ================= USER (CÓ ĐĂNG NHẬP) =================
Route::middleware([Authenticate::class])->group(function () {

    // HOME
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // PRODUCTS
    Route::get('/products', [P::class, 'products'])->name('user.products');
    Route::get('/products/{id}', [UserProductController::class, 'show'])->name('user.products.detail'); // THÊM MỚI: Xem chi tiết SP

    // ================= CHECKOUT (MỚI: LUỒNG MUA NGAY) =================
    Route::prefix('checkout')->name('user.checkout.')->group(function () {
        Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buyNow');    // Bấm nút Mua ngay
        Route::get('/', [CheckoutController::class, 'index'])->name('index');             // Trang nhập thông tin nhận hàng
        Route::post('/process', [CheckoutController::class, 'placeOrder'])->name('process'); // Nút xác nhận mua cuối cùng
    });

    // ================= CART =================
    Route::prefix('cart')->name('user.cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
        Route::put('/{id}', [CartController::class, 'update'])->name('update');
    });

    // ================= ORDERS =================
    Route::prefix('orders')->name('user.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/checkout', [OrderController::class, 'store'])->name('store');
        Route::post('/confirm', [OrderController::class, 'confirm'])->name('confirm');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
    });

    // ================= PROFILE =================
    Route::prefix('profile')->name('user.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/address/store', [ProfileController::class, 'storeAddress'])->name('address.store');
    });

    // ================= WALLET =================
    Route::prefix('wallet')->name('user.wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    });
});