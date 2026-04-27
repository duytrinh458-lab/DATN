<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Bước 1: Hiển thị danh sách đơn hàng đã mua
     */
    public function index()
    {
        // Lấy danh sách đơn hàng của user đang đăng nhập
        $orders = Order::where('user_id', Auth::id())
                       ->orderBy('created_at', 'desc')
                       ->get();

        return view('User.orders.index', compact('orders'));
    }

    /**
     * Bước 2: Nhận từ nút "Mua ngay" ở trang sản phẩm 
     * Chuyển hướng sang trang xác nhận (Checkout)
     */
    public function store(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        
        // Trả về view checkout và truyền dữ liệu sản phẩm sang
        return view('User.orders.checkout', compact('product'));
    }

    /**
     * Bước 3: Xác nhận đặt hàng từ trang Checkout
     * Lưu vào Database (Bảng orders và order_items)
     */
    public function confirm(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $user = Auth::user();

        // Kiểm tra đăng nhập
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        // Kiểm tra địa chỉ (Vì DB yêu cầu address_id không được null)
        if (!$user->address_id) {
            return redirect()->back()->with('error', 'Vui lòng cập nhật địa chỉ giao hàng trong hồ sơ!');
        }

        DB::beginTransaction();
        try {
            // 1. Tạo đơn hàng (Bảng orders)
            $order = new Order();
            $order->order_code = 'ORD-' . strtoupper(Str::random(10));
            $order->user_id = $user->id;
            $order->address_id = $user->address_id;
            $order->subtotal = $product->sale_price;
            $order->shipping_fee = 0; // Tùy chỉnh nếu có phí ship
            $order->total = $product->sale_price;
            $order->status = 'pending';
            $order->save();

            // 2. Lưu chi tiết (Bảng order_items)
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $product->id;
            $orderItem->quantity = 1;
            $orderItem->unit_price = $product->sale_price;
            $orderItem->total_price = $product->sale_price;
            $orderItem->save();

            DB::commit();
            return redirect()->route('user.orders.index')->with('success', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

        /**
        * Bước 4: Xem chi tiết một đơn hàng cụ thể
        */
public function show($id)
{
    // Lấy thông tin đơn hàng cùng với các sản phẩm bên trong (orderItems)
    // Lưu ý: Bạn nên thiết lập relationship 'orderItems' trong Model Order trước
    $order = Order::with(['orderItems.product'])->where('user_id', Auth::id())->findOrFail($id);

    return view('User.orders.show', compact('order'));
}
}