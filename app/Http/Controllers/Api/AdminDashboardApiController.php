<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;

class AdminDashboardApiController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::count();

        $totalRevenue = Order::where('status', 'completed')
            ->sum('total');

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê dashboard thành công',
            'data' => [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_users' => $totalUsers,
                'total_revenue' => $totalRevenue
            ]
        ]);
    }
}