<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckFirstLogin
{
    public function handle(Request $request, Closure $next)
    {
        // Xét hỏi: Nếu đã đăng nhập VÀ cờ đăng nhập lần đầu bằng 1
        if (Auth::check() && Auth::user()->is_first_login == 1) {
            // Đẩy về trang đổi mật khẩu kèm thông báo
            return redirect()->route('password.change')->with('error', 'Vui lòng đổi mật khẩu trong lần đăng nhập đầu tiên để bảo vệ tài khoản!');
        }

        // Cho phép đi tiếp vào trang web
        return $next($request);
    }
}