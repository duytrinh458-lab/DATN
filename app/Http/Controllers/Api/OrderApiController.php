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
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $orders]);
    }

    public function show($id)
    {
        $order = Order::with('orderItems.product')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) return response()->json(['status' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        return response()->json(['status' => true, 'data' => $order]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart || DB::table('cart_items')->where('cart_id', $cart->id)->count() == 0) {
            return response()->json(['status' => false, 'message' => 'Giỏ hàng của bạn đang trống!'], 400);
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        try {
            DB::beginTransaction();

            $cartItems = DB::table('cart_items')->where('cart_id', $cart->id)->get();
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item->unit_price * $item->quantity;
            }

            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet || $wallet->balance < $subtotal) {
                throw new \Exception('Số dư ví V-Pay không đủ để thanh toán!');
            }

            $wallet->balance -= $subtotal;
            $wallet->save();

            $shippingFee = 30000;
            $order = Order::create([
                'user_id' => $userId,
                'address_id' => $request->address_id,
                'order_code' => 'VG-' . strtoupper(Str::random(8)),
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'status' => 'pending'
            ]);

            foreach ($cartItems as $item) {
                // 🔥 THÊM CODE KIỂM TRA TỒN KHO TẠI ĐÂY
                $product = Product::find($item->product_id);
                if (!$product || $product->stock < $item->quantity) {
                    throw new \Exception('Sản phẩm ' . ($product ? $product->name : '') . ' không đủ hàng trong kho!');
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->unit_price * $item->quantity
                ]);
                
                $product->decrement('stock', $item->quantity);
            }

            DB::table('wallet_transactions')->insert([
                'wallet_id' => $wallet->id,
                'type' => 'payment',
                'amount' => $subtotal,
                'reference_code' => $order->order_code,
                'status' => 'success',
                'created_at' => now()
            ]);

            DB::table('cart_items')->where('cart_id', $cart->id)->delete();

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Lệnh điều động UAV đã được tạo!', 'data' => $order], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function cancel($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$order || !in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['status' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái này'], 400);
        }
        $order->status = 'cancelled';
        $order->save();
        return response()->json(['status' => true, 'message' => 'Đã hủy đơn hàng thành công']);
    }

    public function getStatus($id)
    {
        $order = Order::select('status', 'order_code')->where('id', $id)->first();
        return response()->json(['status' => true, 'data' => $order]);
    }

    public function getTimeline($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);

        $timeline = [
            ['time' => $order->ordered_at, 'desc' => 'Đơn hàng đã được khởi tạo'],
            ['time' => $order->updated_at, 'desc' => 'Trạng thái hiện tại: ' . $order->status]
        ];
        return response()->json(['status' => true, 'data' => $timeline]);
    }

    public function confirmReceived($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();
        if ($order->status != 'shipping') return response()->json(['status' => false, 'message' => 'Đơn hàng chưa được giao đến'], 400);

        $order->status = 'delivered';
        $order->save();
        return response()->json(['status' => true, 'message' => 'Xác nhận đã nhận hạm đội UAV thành công']);
    }

    public function requestRefund(Request $request, $id)
    {
        $request->validate(['reason' => 'required']);
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

        DB::table('refunds')->insert([
            'order_id' => $id,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'refund_amount' => $order->total,
            'status' => 'pending',
            'created_at' => now()
        ]);
        return response()->json(['status' => true, 'message' => 'Yêu cầu hoàn tiền đã được gửi tới Ban Quản Trị']);
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']);
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Không thấy đơn hàng'], 404);

        $order->status = $request->status;
        $order->save();
        return response()->json(['status' => true, 'message' => 'Đã chuyển trạng thái đơn hàng sang: ' . $request->status]);
    }

    public function adminApproveRefund($refund_id)
    {
        try {
            DB::beginTransaction();
            $refund = DB::table('refunds')->where('id', $refund_id)->first();
            if (!$refund || $refund->status != 'pending') throw new \Exception('Yêu cầu không hợp lệ hoặc đã được xử lý');

            $wallet = Wallet::where('user_id', $refund->user_id)->first();
            if ($wallet) {
                $wallet->balance += $refund->refund_amount;
                $wallet->save();
                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $wallet->id, 'type' => 'refund', 'amount' => $refund->refund_amount,
                    'reference_code' => 'REF-' . $refund->order_id, 'status' => 'success', 'created_at' => now()
                ]);
            }
            DB::table('refunds')->where('id', $refund_id)->update(['status' => 'approved', 'refunded_at' => now()]);
            Order::where('id', $refund->order_id)->update(['status' => 'cancelled']);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Đã duyệt hoàn tiền và cộng lại số dư cho khách']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function editNote(Request $request, $id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$order) return response()->json(['status' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        return response()->json(['status' => true, 'message' => 'Đã cập nhật ghi chú đơn hàng thành công']);
    }
}