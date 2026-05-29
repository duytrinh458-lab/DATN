<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class AdminDashboardApiController extends Controller
{
    // 📌 API 56: Lấy thống kê Dashboard (Admin)
    public function index()
    {
        // 🛡️ VÁ LỖI LOGIC: Đếm tách biệt sản phẩm đang hoạt động và tổng số (bao gồm cả đã xóa mềm)
        $activeProducts = Product::where('status', 'active')->count();
        $totalProductsEver = Product::withTrashed()->count(); 

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
                'active_products'    => $activeProducts,
                'total_history_prod' => $totalProductsEver, // Trả thêm số liệu lịch sử
                'total_orders'       => $totalOrders,
                'total_users'        => $totalUsers,
                'total_revenue'      => (float)$totalRevenue,
                'order_stats'        => $orderStats
            ]
        ]);
    }
}