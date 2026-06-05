<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

use App\Models\Product;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\News;

use App\Http\Controllers\AuthController;

// USER
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CategoryController as UserCategoryController;
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
use App\Http\Controllers\Admin\BrandController;

use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| GLOBAL BADGE (ADMIN + USER) - ĐÃ FIX LỖI SEC #7 (TỐI ƯU CACHE & CHẶN N+1)
|--------------------------------------------------------------------------
*/
use Illuminate\Support\Facades\Cache;

View::composer('*', function ($view) {

    $user = Auth::user();

    if (!$user) {
        $view->with([
            'orderPendingCount'       => 0,
            'refundPendingCount'      => 0,
            'transactionPendingCount' => 0,
            'commentPendingCount'     => 0,
            'newsDraftCount'          => 0,
        ]);
        return;
    }


    $view->with([
        'orderPendingCount' => Cache::remember('badge_order_pending', 30, function () {
            return Order::where('status', 'pending')->count();
        }),

        'refundPendingCount' => Cache::remember('badge_refund_pending', 30, function () {
            return Refund::where('status', 'pending')->count();
        }),

        'transactionPendingCount' => Cache::remember('badge_transaction_pending', 30, function () {
            return Transaction::where('status', 'pending')->count();
        }),

        'commentPendingCount' => Cache::remember('badge_comment_pending', 30, function () {
            return Review::where('is_approved', 0)->count();
        }),

        'newsDraftCount' => Cache::remember('badge_news_draft', 30, function () {
            return News::where('status', 'draft')->count();
        }),
    ]);
});

/*
|--------------------------------------------------------------------------
| ADMIN BADGE API
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', AdminMiddleware::class])
    ->get('/admin/badges', function () {

    return response()->json([

    'pendingOrders' => Order::where('status', 'pending')->count(),

    'pendingRefunds' => Refund::where('status', 'pending')->count(),

    'pendingComments' => Review::where('is_read', 0)->count(),

    'pendingTransactions' => Transaction::where('status', 'pending')->count(),

    'draftNews' => News::where('status', 'draft')->count(),

    'lowStockProducts' => Product::where('stock', '<=', 5)->count(),

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

        Route::get('/', [AdminController::class, 'dashboard'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | QR SETTINGS
        |--------------------------------------------------------------------------
        */
        Route::get('/settings/qr', [AdminController::class, 'showQRSettings'])
            ->name('qr.index');

        Route::post('/settings/qr', [AdminController::class, 'updateQR'])
            ->name('qr.update');

        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS
        |--------------------------------------------------------------------------
        */
        Route::get('/transactions', [AdminController::class, 'transactions'])
            ->name('transactions.index');

        Route::post('/transactions/{id}/update-status',
            [AdminController::class, 'updateTransactionStatus'])
            ->name('transactions.updateStatus');

        /*
        |--------------------------------------------------------------------------
        | REFUNDS
        |--------------------------------------------------------------------------
        */
        Route::get('/refunds', [AdminController::class, 'refunds'])
            ->name('refunds.index');

        Route::post('/refunds/{id}/update-status',
            [AdminController::class, 'updateRefundStatus'])
            ->name('refunds.updateStatus');

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS / CATEGORIES / NEWS
        |--------------------------------------------------------------------------
        */
        Route::resource('products', AdminProductController::class)
            ->except(['show']);

        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class)
    ->except(['show']);

        Route::resource('news', NewsController::class);

        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */
        Route::get('/orders',
            [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/orders/{id}',
            [AdminOrderController::class, 'show'])
            ->name('orders.show');

        Route::post('/orders/{id}/update',
            [AdminOrderController::class, 'update'])
            ->name('orders.update');

        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::get('/users/{id}', [UserController::class, 'show'])
            ->name('users.show');

        Route::put('/users/{id}', [UserController::class, 'update'])
            ->name('users.update');

        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->name('users.destroy');

        /*
        |--------------------------------------------------------------------------
        | INTERACTIONS
        |--------------------------------------------------------------------------
        */
        Route::prefix('interactions')
            ->name('interactions.')
            ->group(function () {

                Route::get('/comments',
                    [InteractionController::class, 'comments'])
                    ->name('comments');

                Route::delete('/comments/{id}',
                    [InteractionController::class, 'deleteComment'])
                    ->name('comments.delete');

                Route::post('/comments/reply',
                    [InteractionController::class, 'replyComment'])
                    ->name('comments.reply');

                Route::get('/likes',
                    [InteractionController::class, 'likes'])
                    ->name('likes');

                Route::get('/ratings',
                    [InteractionController::class, 'ratings'])
                    ->name('ratings');

            });

    });

/*
|--------------------------------------------------------------------------
| USER routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME
    |--------------------------------------------------------------------------
    */
    Route::get('/home',
        [HomeController::class, 'index'])
        ->name('home');

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */
    Route::get('/products',
        [ProductController::class, 'products'])
        ->name('user.products');

    Route::get('/products/{id}',
        [ProductController::class, 'show'])
        ->name('user.products.detail');

    Route::post('/products/{id}/comment',
        [ProductController::class, 'storeComment'])
        ->name('user.comment.store');

    Route::post('/comment/update/{id}',
        [ProductController::class, 'updateComment'])
        ->name('user.comment.update');

    Route::delete('/comment/{id}',
        [ProductController::class, 'deleteComment'])
        ->name('user.comment.delete');

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES User
    |--------------------------------------------------------------------------
    */
     Route::get('/categories', [UserCategoryController::class, 'categories'])
        ->name('user.categories');

    Route::get('/categories/{slug}', [UserCategoryController::class, 'byCategory'])
        ->name('user.categories.show');


    /*
    |--------------------------------------------------------------------------
    | NEWS
    |--------------------------------------------------------------------------
    */
    Route::get('/news',
        [UserNewsController::class, 'index'])
        ->name('user.news.index');

    Route::get('/news/{slug}',
        [UserNewsController::class, 'show'])
        ->name('user.news.show');

    /*
    |--------------------------------------------------------------------------
    | CHECKOUT
    |--------------------------------------------------------------------------
    */
    Route::prefix('checkout')
        ->name('user.checkout.')
        ->group(function () {

            Route::post('/buy-now',
                [CheckoutController::class, 'buyNow'])
                ->name('buyNow');

            Route::get('/',
                [CheckoutController::class, 'index'])
                ->name('index');

            Route::post('/process',
                [CheckoutController::class, 'placeOrder'])
                ->name('process');

        });

    /*
    |--------------------------------------------------------------------------
    | CART
    |--------------------------------------------------------------------------
    */
    Route::prefix('cart')
        ->name('user.cart.')
        ->group(function () {

            Route::get('/',
                [CartController::class, 'index'])
                ->name('index');

            Route::post('/add',
                [CartController::class, 'add'])
                ->name('add');

            Route::delete('/{id}',
                [CartController::class, 'destroy'])
                ->name('destroy');

            Route::put('/{id}',
                [CartController::class, 'update'])
                ->name('update');

        });

    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */
    Route::prefix('orders')
        ->name('user.orders.')
        ->group(function () {

            Route::get('/',
                [OrderController::class, 'index'])
                ->name('index');

            Route::get('/{id}',
                [OrderController::class, 'show'])
                ->name('show');

            Route::post('/{id}/cancel',
                [OrderController::class, 'cancel'])
                ->name('cancel');

            Route::get('/{id}/refund',
                [OrderController::class, 'showRefundForm'])
                ->name('refund');

            Route::post('/{id}/refund',
                [OrderController::class, 'submitRefund'])
                ->name('refund.submit');

        });

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')
        ->name('user.profile.')
        ->group(function () {

            Route::get('/',
                [ProfileController::class, 'index'])
                ->name('index');

            Route::post('/update',
                [ProfileController::class, 'update'])
                ->name('update');

            Route::post('/address/store',
                [ProfileController::class, 'storeAddress'])
                ->name('address.store');

            Route::get('/address/{id}/json',
                [ProfileController::class, 'getAddressJson'])
                ->name('address.json');

            Route::put('/address/{id}',
                [ProfileController::class, 'updateAddress'])
                ->name('address.update');

            Route::delete('/address/{id}',
                [ProfileController::class, 'destroyAddress'])
                ->name('address.destroy');

            Route::post('/address/{id}/set-default',
                [ProfileController::class, 'setDefaultAddress'])
                ->name('address.setDefault');

        });

    /*
    |--------------------------------------------------------------------------
    | WALLET
    |--------------------------------------------------------------------------
    */
    Route::prefix('wallet')
        ->name('user.wallet.')
        ->group(function () {

            Route::get('/',
                [WalletController::class, 'index'])
                ->name('index');

            Route::post('/deposit',
                [WalletController::class, 'deposit'])
                ->name('deposit');

            Route::post('/withdraw',
                [WalletController::class, 'withdraw'])
                ->name('withdraw');

        });

});