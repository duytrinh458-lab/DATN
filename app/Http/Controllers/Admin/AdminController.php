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

        // ✅ FIX: đúng cột + đúng status
        $revenue = Order::where('status', 'delivered')->sum('total');

        // 🔥 Sản phẩm bán chạy nhất
        $bestProduct = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();

        return view('Admin.dashboard', compact(
            'productCount',
            'orderCount',
            'userCount',
            'revenue',
            'bestProduct'
        ));
    }
}