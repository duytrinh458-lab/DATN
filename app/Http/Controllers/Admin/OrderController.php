<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 📋 Danh sách đơn hàng
    public function index()
    {
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.full_name', 'users.phone')
            ->orderBy('orders.id', 'desc')
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    // 🔍 Chi tiết đơn
    public function show($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();

        $items = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $id)
            ->select(
                'products.name',
                'order_items.quantity',
                'order_items.unit_price',
                'order_items.total_price'
            )
            ->get();

        return view('admin.orders.show', compact('order', 'items'));
    }

    // 🔄 Cập nhật trạng thái (FIX AN TOÀN)
    public function update(Request $request, $id)
    {
        // ✅ CHẶN SAI STATUS TRƯỚC KHI UPDATE
        $request->validate([
            'status' => 'required|in:pending,processing,shipping,delivered'
        ]);

        DB::table('orders')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }
}