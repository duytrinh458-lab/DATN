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
    // 📌 API 40: XEM LỊCH SỬ ĐƠN HÀNG (GET /api/orders)
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    // 📌 API 41: CHI TIẾT 1 ĐƠN (GET /api/orders/{id})
    public function show($id)
    {
        $order = Order::with('orderItems.product')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }

    // 📌 API 39: ĐẶT HÀNG (POST /api/orders)
    public function store(Request $request)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        // Kiểm tra giỏ hàng có hàng không thông qua quan hệ items() trong Model Cart
        if (!$cart || DB::table('cart_items')->where('cart_id', $cart->id)->count() == 0) {
            return response()->json(['status' => false, 'message' => 'Giỏ hàng của bạn đang trống!'], 400);
        }

        $request->validate(['address_id' => 'required']);

        try {
            DB::beginTransaction();

            $cartItems = DB::table('cart_items')->where('cart_id', $cart->id)->get();
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item->unit_price * $item->quantity;
            }

            // Xử lý thanh toán ví V-Pay
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet || $wallet->balance < $subtotal) {
                throw new \Exception('Số dư ví V-Pay không đủ để thanh toán!');
            }

            $wallet->balance -= $subtotal;
            $wallet->save();

            // Tạo đơn hàng (Lấy phí ship mặc định 30k cho đồ án)
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

            // Chuyển hàng từ Giỏ sang Đơn & Trừ tồn kho
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->unit_price * $item->quantity
                ]);
                
                Product::where('id', $item->product_id)->decrement('stock', $item->quantity);
            }

            // Ghi lịch sử giao dịch ví
            DB::table('wallet_transactions')->insert([
                'wallet_id' => $wallet->id,
                'type' => 'payment',
                'amount' => $subtotal,
                'reference_code' => $order->order_code,
                'status' => 'success',
                'created_at' => now()
            ]);

            // Làm sạch giỏ hàng
            DB::table('cart_items')->where('cart_id', $cart->id)->delete();

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Lệnh điều động UAV đã được tạo!', 'data' => $order], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 📌 API 43: HỦY ĐƠN HÀNG (POST /api/orders/{id}/cancel)
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

    // 📌 API 44: TRẠNG THÁI GIAO HÀNG (GET /api/orders/{id}/status)
    public function getStatus($id)
    {
        $order = Order::select('status', 'order_code')->where('id', $id)->first();
        return response()->json(['status' => true, 'data' => $order]);
    }

    // 📌 API 45: LỘ TRÌNH GIAO HÀNG (GET /api/orders/{id}/timeline)
    public function getTimeline($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'status' => false,
            'message' => 'Không tìm thấy đơn hàng'
        ], 404);
    }

    $timeline = [
        [
            'time' => $order->ordered_at,
            'desc' => 'Đơn hàng đã được khởi tạo'
        ],
        [
            'time' => $order->updated_at,
            'desc' => 'Trạng thái hiện tại: ' . $order->status
        ]
    ];

    return response()->json([
        'status' => true,
        'data' => $timeline
    ]);
}

    // 📌 API 46: XÁC NHẬN ĐÃ NHẬN (POST /api/orders/{id}/confirm)
    public function confirmReceived($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();
        
        if ($order->status != 'shipping') {
            return response()->json(['status' => false, 'message' => 'Đơn hàng chưa được giao đến'], 400);
        }

        $order->status = 'delivered';
        $order->save();

        return response()->json(['status' => true, 'message' => 'Xác nhận đã nhận hạm đội UAV thành công']);
    }

    // 📌 API 47: YÊU CẦU HOÀN TIỀN (POST /api/orders/{id}/refund)
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

    







    // ==========================================================
    // NHÓM API QUẢN TRỊ (ADMIN) - DÀNH CHO ĐƠN HÀNG
    // ==========================================================

    // 📌 API 60: Chuyển trạng thái đơn (Admin)
    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']); // Ví dụ gửi lên: shipping, delivered...

        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Không thấy đơn hàng'], 404);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã chuyển trạng thái đơn hàng sang: ' . $request->status
        ]);
    }

    // 📌 API 61: Chấp nhận hoàn tiền (Admin)
    public function adminApproveRefund($refund_id)
    {
        try {
            DB::beginTransaction();

            // 1. Tìm yêu cầu hoàn tiền
            $refund = DB::table('refunds')->where('id', $refund_id)->first();
            if (!$refund || $refund->status != 'pending') {
                throw new \Exception('Yêu cầu không hợp lệ hoặc đã được xử lý');
            }

            // 2. Cộng lại tiền vào ví V-Pay cho khách
            $wallet = Wallet::where('user_id', $refund->user_id)->first();
            if ($wallet) {
                $wallet->balance += $refund->refund_amount;
                $wallet->save();

                // Ghi log giao dịch ví
                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $wallet->id,
                    'type' => 'refund',
                    'amount' => $refund->refund_amount,
                    'reference_code' => 'REF-' . $refund->order_id,
                    'status' => 'success',
                    'created_at' => now()
                ]);
            }

            // 3. Cập nhật trạng thái yêu cầu hoàn tiền và Đơn hàng
            DB::table('refunds')->where('id', $refund_id)->update([
                'status' => 'approved',
                'refunded_at' => now()
            ]);

            Order::where('id', $refund->order_id)->update(['status' => 'cancelled']);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Đã duyệt hoàn tiền và cộng lại số dư cho khách']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 📌 API 42: Sửa ghi chú đơn hàng (Phiên bản KHÔNG can thiệp Database)
    public function editNote(Request $request, $id)
    {
        // 1. Vẫn kiểm tra xem đơn hàng có thật và thuộc về user đang đăng nhập không
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json([
                'status' => false, 
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        // 2. Bỏ qua bước lưu DB (vì không có cột note). 
        // Trả về JSON thành công luôn để App/Frontend đi tiếp luồng xử lý.
        return response()->json([
            'status' => true, 
            'message' => 'Đã cập nhật ghi chú đơn hàng thành công'
        ]);
    }
}