<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardApiController extends Controller
{
    // 📌 API 56: Lấy thống kê Dashboard (Admin)
    public function index()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::where('role', 'customer')->count(); // Chỉ đếm khách hàng

        // Tính doanh thu từ các đơn hàng đã giao thành công (delivered)
        $totalRevenue = Order::where('status', 'delivered')->sum('total');

        // Thống kê số lượng đơn theo trạng thái để vẽ biểu đồ
        $orderStats = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy thống kê hạm đội thành công',
            'data' => [
                'total_products' => $totalProducts,
                'total_orders'   => $totalOrders,
                'total_users'    => $totalUsers,
                'total_revenue'  => (float)$totalRevenue,
                'order_stats'    => $orderStats
            ]
        ]);
    }
}