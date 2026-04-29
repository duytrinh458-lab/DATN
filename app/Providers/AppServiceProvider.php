<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;

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
        // Share wallet balance toàn hệ thống (header, layout, etc.)
        View::composer('*', function ($view) {

            if (Auth::check()) {

                $wallet = Wallet::where('user_id', Auth::id())->first();

                $view->with('walletBalance', $wallet->balance ?? 0);

            } else {
                $view->with('walletBalance', 0);
            }
        });
    }
}