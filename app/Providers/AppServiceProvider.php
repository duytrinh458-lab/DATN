<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

use App\Models\Wallet;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Transaction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination Bootstrap 5
        Paginator::useBootstrapFive();

        // Share dữ liệu toàn hệ thống
        View::composer('*', function ($view) {

            /*
            |--------------------------------------------------------------------------
            | WALLET BALANCE
            |--------------------------------------------------------------------------
            */

            $balance = 0;

            if (Auth::check()) {

                $wallet = Wallet::where(
                    'user_id',
                    Auth::id()
                )->first();

                $balance = $wallet?->balance ?? 0;
            }


            /*
            |--------------------------------------------------------------------------
            | ADMIN BADGES
            |--------------------------------------------------------------------------
            */

            // Sản phẩm hết hàng
            $outOfStockCount = Product::where(
                'stock',
                0
            )->count();


            // Đơn hàng chưa duyệt
            $pendingOrdersCount = Order::where(
                'status',
                'pending'
            )->count();


            // Yêu cầu hoàn hàng chưa xử lý
            $pendingRefunds = DB::table('refunds')
                ->where(
                    'status',
                    'pending'
                )
                ->count();


            // Đánh giá mới chưa đọc
            $newReviewsCount = Review::where(
                'is_read',
                0
            )->count();


            // Yêu cầu nạp ví QR
            $walletQrCount = Transaction::where(
                    'type',
                    'deposit'
                )
                ->where(
                    'status',
                    'pending'
                )
                ->count();


            /*
            |--------------------------------------------------------------------------
            | SHARE TO ALL VIEWS
            |--------------------------------------------------------------------------
            */

            $view->with([

                // Wallet
                'walletBalance' => $balance,

                // Badges
                'outOfStockCount' => $outOfStockCount,

                'pendingOrdersCount' => $pendingOrdersCount,

                'pendingRefunds' => $pendingRefunds,

                'newReviewsCount' => $newReviewsCount,

                'walletQrCount' => $walletQrCount,

            ]);
        });
    }
}