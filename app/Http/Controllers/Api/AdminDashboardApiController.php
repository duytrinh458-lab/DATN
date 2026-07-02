<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * MỤC ĐÍCH FILE: 
 * File này chứa API cung cấp toàn bộ dữ liệu thống kê tổng quan của hệ thống dành riêng cho trang quản trị (Admin Dashboard).
 * Dữ liệu này giúp người quản lý có cái nhìn toàn diện về tình hình hoạt động của cửa hàng (sản phẩm, đơn hàng, người dùng, doanh thu).
 */
class AdminDashboardApiController extends Controller
{
    // 📌 API 56: Lấy thống kê Dashboard (Admin)
    // VAI TRÒ: Thu thập, tổng hợp các chỉ số quan trọng từ database và trả về định dạng JSON để frontend vẽ biểu đồ hoặc hiển thị số liệu.
    public function index()
    {
        // 🛡️ VÁ LỖI LOGIC: Đếm tách biệt sản phẩm đang hoạt động và tổng số (bao gồm cả đã xóa mềm)
        
        // BIẾN QUAN TRỌNG: $activeProducts đếm số lượng các sản phẩm đang có trạng thái hoạt động ('active') để hiển thị trên app/web.
        $activeProducts = Product::where('status', 'active')->count();
        
        // BIẾN QUAN TRỌNG: $totalProductsEver đếm tất cả sản phẩm từng tồn tại trong hệ thống.
        // Giải thích: Hàm withTrashed() giúp lấy ra cả những sản phẩm đã bị xóa tạm thời (xóa mềm - soft delete) mà bình thường lệnh count() sẽ bỏ qua.
        $totalProductsEver = Product::withTrashed()->count(); 

        // BIẾN QUAN TRỌNG: $totalOrders đếm tổng số lượng tất cả các đơn hàng đã được tạo từ trước đến nay.
        $totalOrders = Order::count();
        
        // BIẾN QUAN TRỌNG: $totalUsers đếm số lượng người dùng đăng ký tài khoản dưới vai trò là khách hàng ('customer'), loại bỏ tài khoản quản trị viên hoặc nhân viên.
        $totalUsers = User::where('role', 'customer')->count(); // Chỉ đếm khách hàng

        // TRUY VẤN: Tính tổng số tiền thu được ($totalRevenue). 
        // Giải thích: Hàm sum('total') sẽ cộng dồn tất cả giá trị tiền ở cột 'total' của những đơn hàng đã được giao thành công (trạng thái là 'delivered').
        $totalRevenue = Order::where('status', 'delivered')->sum('total');

        // TRUY VẤN PHỨC TẠP: Thống kê số lượng đơn hàng theo từng trạng thái để phục vụ cho việc vẽ biểu đồ hình tròn/biểu đồ cột.
        // Ý tưởng luồng đi: 
        // 1. Nhóm (groupBy) tất cả các đơn hàng có cùng trạng thái lại với nhau (Ví dụ: Chờ xử lý, Đang giao, Đã giao, Đã hủy).
        // 2. Sử dụng câu lệnh SQL thuần DB::raw('count(*) as total') để đếm xem trong từng nhóm trạng thái đó có bao nhiêu đơn hàng.
        $orderStats = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Lấy thống kê hạm đội thành công',
            'data'    => [
                'active_products'    => $activeProducts,
                'total_history_prod' => $totalProductsEver, // Trả thêm số liệu lịch sử
                'total_orders'       => $totalOrders,
                'total_users'        => $totalUsers,
                'total_revenue'      => (float) $totalRevenue, // Ép kiểu dữ liệu về dạng số thập phân để đảm bảo độ chính xác
                'order_stats'        => $orderStats
            ]
        ]);
    }
}