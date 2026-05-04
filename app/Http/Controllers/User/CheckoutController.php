<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Bước 1: Nhận sản phẩm từ nút "Mua ngay" và lưu vào Session
    public function buyNow(Request $request)
    {
        $product = Product::with('images')->findOrFail($request->product_id);

        // Lưu thông tin tạm thời để sang trang thanh toán hiển thị
        session(['checkout_item' => [
            'product_id' => $product->id,
            'name'       => $product->name,
            'price'      => $product->sale_price,
            'quantity'   => $request->quantity ?? 1,
            'image'      => $product->images->first()->image_url ?? 'default.jpg'
        ]]);

        return redirect()->route('user.checkout.index');
    }

    // Bước 2: Hiển thị giao diện thanh toán (Image 10)
   // Bước 2: Hiển thị giao diện thanh toán
    public function index()
    {
        $item = session('checkout_item');
        if (!$item) return redirect()->route('user.products')->with('error', 'Hết phiên làm việc.');

        // Đã sửa lại đường dẫn view để trỏ đúng vào thư mục orders
        return view('User.orders.checkout', compact('item')); 
    }

    // Bước 3: Xử lý khi khách bấm "Xác nhận thanh toán"
    public function placeOrder(Request $request)
    {
        $item = session('checkout_item');
        if (!$item) return back()->with('error', 'Lỗi dữ liệu.');

        try {
            DB::beginTransaction();

            // 1. Tạo đơn hàng mới trong bảng orders
            $order = new Order();
            $order->user_id      = Auth::id() ?? 4; // Tạm thời để ID 4 nếu chưa làm Login
            $order->order_code   = 'VG-' . strtoupper(Str::random(8));
            $order->subtotal     = $item['price'] * $item['quantity'];
            $order->total        = $order->subtotal + 30000; // Phí ship mặc định 30k
            $order->status       = 'pending';
            // Duy cần bổ sung address_id dựa trên form khách nhập
            $order->address_id   = 1; 
            $order->save();

            // 2. Lưu chi tiết vào bảng order_items
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price'=> $item['price'] * $item['quantity']
            ]);

            DB::commit();
            session()->forget('checkout_item'); // Xóa session sau khi mua xong

            return redirect()->route('user.products')->with('success', 'Đơn hàng ' . $order->order_code . ' đã được khởi tạo!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }
}