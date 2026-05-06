<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wallet;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderApiController extends Controller
{
    // 📌 XEM LỊCH SỬ ĐƠN HÀNG
    public function index()
    {
        $orders = Order::with('orderItems.product')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    // 📌 ĐẶT HÀNG (CHECKOUT) TỪ GIỎ HÀNG
    public function store(Request $request)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json(['status' => false, 'message' => 'Giỏ hàng trống!'], 400);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += $item->unit_price * $item->quantity;
            }

            // Xử lý ví V-Pay (Nếu có)
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if ($wallet && $wallet->balance < $subtotal) {
                throw new \Exception('Số dư ví V-Pay không đủ!');
            }
            if ($wallet) {
                $wallet->balance -= $subtotal;
                $wallet->save();
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $userId,
                'order_code' => 'VG-' . strtoupper(Str::random(8)),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
                'address_id' => $request->address_id ?? 1 // Tạm hardcode hoặc lấy từ request
            ]);

            // Chuyển CartItem sang OrderItem & Trừ kho
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->unit_price * $item->quantity
                ]);
                
                Product::where('id', $item->product_id)->decrement('stock', $item->quantity);
            }

            // Xóa giỏ hàng sau khi đặt thành công
            CartItem::where('cart_id', $cart->id)->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Tạo lệnh điều động thành công',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 📌 HỦY ĐƠN HÀNG
    public function cancel($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$order || $order->status != 'pending') {
            return response()->json(['status' => false, 'message' => 'Không thể hủy đơn hàng này'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['status' => true, 'message' => 'Hủy đơn hàng thành công']);
    }
}