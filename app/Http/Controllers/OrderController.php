<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * 1. Hiển thị danh sách đơn hàng của người dùng
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
                       ->orderBy('ordered_at', 'desc')
                       ->get();

        return view('User.orders.index', compact('orders'));
    }

    /**
     * 2. Xem chi tiết một đơn hàng cụ thể
     */
    public function show($id)
    {
        // Eager load các relations cần thiết: Chi tiết đơn, Ảnh sản phẩm, và Địa chỉ giao hàng
        $order = Order::with(['orderItems.product.images', 'address'])
                      ->where('user_id', Auth::id())
                      ->findOrFail($id);

        return view('User.orders.show', compact('order'));
    }

    /**
     * 3. Hủy đơn hàng (Chỉ cho phép khi status = pending)
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // Chặn nếu đơn hàng đã được duyệt hoặc đang giao
        if ($order->status !== 'pending') {
            return back()->with('error', 'CẢNH BÁO: Chỉ có thể hủy đơn hàng khi đang ở trạng thái Chờ duyệt!');
        }

        DB::beginTransaction();
        try {
            // Đổi trạng thái thành cancelled và lưu lý do (tùy chọn)
            $order->status = 'cancelled';
            
            $order->save();

            // QUAN TRỌNG: Hoàn lại số lượng UAV về kho
            foreach ($order->orderItems as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }

            DB::commit();
            return back()->with('success', 'Đã hủy đơn hàng ' . $order->order_code . '. Các thiết bị UAV đã được trả về kho tổng.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hệ thống gián đoạn: ' . $e->getMessage());
        }
    }
}