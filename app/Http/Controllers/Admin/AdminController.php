<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $productCount = Product::count();
        $orderCount   = Order::count();
        $userCount    = User::count();

        // Doanh thu đơn đã giao
        $revenue = Order::where('status', 'delivered')->sum('total');

        // Sản phẩm bán chạy
        $bestProduct = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();


        // 💬 COMMENT = reviews (theo DB của bạn)
        $commentCount = DB::table('reviews')->count();

        return view('Admin.dashboard', compact(
            'productCount',
            'orderCount',
            'userCount',
            'revenue',
            'bestProduct',
            'commentCount'
        ));
    }
}