<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // 💡 Kể cả đã có Token, nhưng Role không phải Admin thì chặn luôn
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'Forbidden: Bạn không có quyền quản trị hệ thống!'
        ], 403);
    }
}