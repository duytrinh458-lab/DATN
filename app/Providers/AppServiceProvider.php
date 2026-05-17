<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use Illuminate\Pagination\Paginator;

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
        // ✅ Pagination UI (QUAN TRỌNG)
        Paginator::useBootstrapFive();

        // Share wallet balance toàn hệ thống
        View::composer('*', function ($view) {

            $balance = 0;

            if (Auth::check()) {
                $wallet = Wallet::where('user_id', Auth::id())->first();
                $balance = $wallet?->balance ?? 0;
            }

            $view->with('walletBalance', $balance);
        });
    }
}