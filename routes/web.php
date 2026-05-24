<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

use App\Models\Order;
use App\Models\Refund;
use App\Models\Transaction;

use App\Http\Controllers\AuthController;

// USER
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\NewsController as UserNewsController;

// ADMIN
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InteractionController;
use App\Http\Controllers\Admin\NewsController;

use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| GLOBAL BADGE (ADMIN + USER)
|--------------------------------------------------------------------------
*/
View::composer('*', function ($view) {

    $user = Auth::user();

    if (!$user) {
        $view->with([
            'orderPendingCount' => 0,
            'refundPendingCount' => 0,
            'transactionPendingCount' => 0,
        ]);
        return;
    }

    $view->with([
        'orderPendingCount' => Order::where('status', 'pending')->count(),
        'refundPendingCount' => Refund::where('status', 'pending')->count(),
        'transactionPendingCount' => Transaction::where('status', 'pending')->count(),
    ]);
});

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {

    Route::get('/login', 'showLogin')->name('login');
    Route::get('/register', 'showRegister')->name('register');
    Route::get('/forgot', 'showForgot')->name('forgot');

    Route::post('/login', 'login');
    Route::post('/send-otp-register', 'sendOtpRegister');
    Route::post('/verify-otp-register', 'verifyOtpRegister');
    Route::post('/forgot-password/send-otp', 'sendOtpForgotPassword');
    Route::post('/forgot-password/verify-otp', 'verifyOtpForgotPassword');

    Route::get('/change-password', 'showChangePasswordForm')->name('password.change');
    Route::post('/change-password', 'updatePassword')->name('password.change.update');

    Route::get('/register/reset-phone', function () {
        Session::forget('phone_step1');
        return redirect('/register');
    });

    Route::get('/forgot-password/reset-session', function () {
        Session::forget('forgot_phone');
        return redirect('/forgot');
    });
});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', AdminMiddleware::class])
    ->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/settings/qr', [AdminController::class, 'showQRSettings'])->name('qr.index');
        Route::post('/settings/qr', [AdminController::class, 'updateQR'])->name('qr.update');

        Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions.index');
        Route::post('/transactions/{id}/update-status', [AdminController::class, 'updateTransactionStatus'])->name('transactions.updateStatus');

        Route::get('/refunds', [AdminController::class, 'refunds'])->name('refunds.index');
        Route::post('/refunds/{id}/update-status', [AdminController::class, 'updateRefundStatus'])->name('refunds.updateStatus');

        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('categories', CategoryController::class);
        Route::resource('news', NewsController::class);

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/update', [AdminOrderController::class, 'update'])->name('orders.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::prefix('interactions')->name('interactions.')->group(function () {

            Route::get('/comments', [InteractionController::class, 'comments'])->name('comments');
            Route::delete('/comments/{id}', [InteractionController::class, 'deleteComment'])->name('comments.delete');
            Route::post('/comments/reply', [InteractionController::class, 'replyComment'])->name('comments.reply');
            Route::get('/likes', [InteractionController::class, 'likes'])->name('likes');
            Route::get('/ratings', [InteractionController::class, 'ratings'])->name('ratings');

        });

    });

/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/products', [ProductController::class, 'products'])->name('user.products');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('user.products.detail');

    Route::post('/products/{id}/comment', [ProductController::class, 'storeComment'])->name('user.comment.store');
    Route::post('/comment/update/{id}', [ProductController::class, 'updateComment'])->name('user.comment.update');
    Route::delete('/comment/{id}', [ProductController::class, 'deleteComment'])->name('user.comment.delete');

    Route::get('/categories', [ProductController::class, 'categories'])->name('user.categories');
    Route::get('/categories/{id}', [ProductController::class, 'byCategory'])->name('user.categories.show');

    Route::get('/news', [UserNewsController::class, 'index'])->name('user.news.index');
    Route::get('/news/{slug}', [UserNewsController::class, 'show'])->name('user.news.show');

    Route::prefix('checkout')->name('user.checkout.')->group(function () {
        Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buyNow');
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'placeOrder'])->name('process');
    });

    Route::prefix('cart')->name('user.cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
        Route::put('/{id}', [CartController::class, 'update'])->name('update');
    });

    Route::prefix('orders')->name('user.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/refund', [OrderController::class, 'showRefundForm'])->name('refund');
        Route::post('/{id}/refund', [OrderController::class, 'submitRefund'])->name('refund.submit');
    });

    Route::prefix('profile')->name('user.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        
        // --- CÁC ROUTE ĐỊA CHỈ BỊ THIẾU CẦN THÊM VÀO ---
        Route::post('/address/store', [ProfileController::class, 'storeAddress'])->name('address.store');
        Route::get('/address/{id}/json', [ProfileController::class, 'getAddressJson'])->name('address.json');
        Route::put('/address/{id}', [ProfileController::class, 'updateAddress'])->name('address.update');
        Route::delete('/address/{id}', [ProfileController::class, 'destroyAddress'])->name('address.destroy');
        Route::post('/address/{id}/set-default', [ProfileController::class, 'setDefaultAddress'])->name('address.setDefault');
    });

    Route::prefix('wallet')->name('user.wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    });

});