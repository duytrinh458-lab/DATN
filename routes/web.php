<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;

// AUTH
use App\Http\Controllers\AuthController;

// USER CONTROLLERS
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\ProductController; // ✅ FIX ĐÚNG CONTROLLER
use App\Http\Controllers\User\CheckoutController;

// ORDER
use App\Http\Controllers\OrderController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;

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


// ================= LOGOUT =================
Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');


// ================= ADMIN =================
Route::prefix('admin')
    ->name('admin.')
    ->middleware([Authenticate::class, AdminMiddleware::class])
    ->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // PRODUCTS
        Route::resource('products', AdminProductController::class)->except(['show']);

        // CATEGORIES
        Route::resource('categories', CategoryController::class);

        // ORDERS
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/update', [AdminOrderController::class, 'update'])->name('orders.update');

        // USERS
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    });


// ================= USER =================
Route::middleware([Authenticate::class])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // PRODUCTS
    Route::get('/products', [ProductController::class, 'products'])->name('user.products');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('user.products.detail');

    // CATEGORIES
    // CATEGORY USER
Route::get('/categories', [ProductController::class, 'categories'])->name('user.categories');
Route::get('/categories/{id}', [ProductController::class, 'byCategory'])->name('user.categories.show');

    // CHECKOUT
    Route::prefix('checkout')->name('user.checkout.')->group(function () {
        Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buyNow');
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'placeOrder'])->name('process');
    });

    // CART
    Route::prefix('cart')->name('user.cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
        Route::put('/{id}', [CartController::class, 'update'])->name('update');
    });

    // ORDERS
    Route::prefix('orders')->name('user.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    // PROFILE
    Route::prefix('profile')->name('user.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/address/store', [ProfileController::class, 'storeAddress'])->name('address.store');
        Route::post('/address/{id}/set-default', [ProfileController::class, 'setDefaultAddress'])->name('address.setDefault');
    });

    // WALLET
    Route::prefix('wallet')->name('user.wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    });

});