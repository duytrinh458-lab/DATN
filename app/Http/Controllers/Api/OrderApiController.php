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

    // 📌 API: Hủy đơn hàng và hoàn tiền vào ví (PUT hoặc POST /api/orders/{id}/cancel)
public function cancel($id) 
{
    // 1. Tìm đúng đơn hàng của User đang đăng nhập
    $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

    if (!$order) {
        return response()->json([
            'status'  => false,
            'message' => 'Không tìm thấy đơn hàng!'
        ], 404);
    }

    // 2. ĐIỀU KIỆN CHẶN BẢO VỆ: Chỉ cho phép hủy khi đơn hàng đang ở trạng thái chờ xử lý (pending/processing)
    // Tránh trường hợp đơn hàng đã giao thành công (delivered) hoặc đã hủy rồi vẫn cố tình gọi API để "hack" hoàn tiền
    if (in_array($order->status, ['cancelled', 'delivered', 'shipped'])) {
        return response()->json([
            'status'  => false,
            'message' => 'Đơn hàng này không thể hủy do đã được xử lý, đang giao hoặc đã hủy trước đó.'
        ], 400);
    }

    // 3. TIẾN HÀNH HỦY ĐƠN VÀ HOÀN TIỀN (Sử dụng Transaction bảo mật)
    DB::beginTransaction();

    try {
        // Lấy ví V-Pay của user và dùng lockForUpdate() để khóa dòng dữ liệu, chống bấm hủy liên tiếp
        $wallet = Wallet::where('user_id', Auth::id())->lockForUpdate()->first();

        if ($wallet) {
            // Cộng tiền trả lại vào số dư ví của khách 
            // 💡 Lưu ý: Bạn kiểm tra lại trong DB xem cột tổng tiền là 'total' hay 'total_price' để điền cho đúng nhé
            $wallet->balance += $order->total; 
            $wallet->save();

            // Chèn lịch sử giao dịch hoàn tiền vào bảng wallet_transactions (Đồng bộ logic với WalletApiController)
            DB::table('wallet_transactions')->insert([
                'wallet_id'      => $wallet->id,
                'type'           => 'refund', // Loại giao dịch: Hoàn tiền (hoặc 'deposit' tùy bạn quy định, nhưng 'refund' sẽ tường minh hơn)
                'amount'         => $order->total,
                'reference_code' => 'REF-' . $order->id . '-' . time(),
                'status'         => 'completed', // Khác với nạp tiền cần duyệt, hoàn tiền do hủy đơn hệ thống tự cộng và đổi thành 'completed' luôn
                'created_at'     => now()
            ]);
        }

        // Cập nhật trạng thái đơn hàng sang đã hủy
        $order->status = 'cancelled';
        $order->save();

        // Hoàn thành phiên giao dịch an toàn
        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Hủy đơn hàng thành công. Tiền đã được hoàn lại vào ví V-Pay của bạn!'
        ]);

    } catch (\Exception $e) {
        // Nếu có bất kỳ lỗi phát sinh nào (ví dụ lỗi SQL), lập tức khôi phục lại dữ liệu ban đầu, không lo mất mát tiền bạc
        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Có lỗi xảy ra trong quá trình hủy đơn và hoàn tiền!',
            'error'   => $e->getMessage() // Hiện mã lỗi để bạn dễ debug khi chạy thử
        ], 500);
    }
}

    public function getStatus($id)
    {
        $user = Auth::user();

        // 🛡️ Xây dựng query cơ sở
        $query = Order::select('id', 'user_id', 'status', 'order_code')->where('id', $id);

        // 🛡️ Nếu không phải Admin, bắt buộc đơn hàng phải thuộc về user đang đăng nhập
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        // 💡 Trả về 404 thay vì 403 để kẻ tấn công không biết ID này có tồn tại trong hệ thống hay không
        if (!$order) {
            return response()->json([
                'status' => false, 
                'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập.'
            ], 404);
        }

        return response()->json([
            'status' => true, 
            'data' => [
                'order_code' => $order->order_code,
                'status'     => $order->status
            ]
        ]);
    }

    // 📌 Lấy timeline giao hàng (ĐÃ VÁ LỖI IDOR)
    public function getTimeline($id)
    {
        $user = Auth::user();

        // 🛡️ Xây dựng query cơ sở
        $query = Order::where('id', $id);

        // 🛡️ Nếu không phải Admin, bắt buộc đơn hàng phải thuộc về user đang đăng nhập
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (!$order) {
            return response()->json([
                'status' => false, 
                'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập.'
            ], 404);
        }

        $timeline = [
            ['time' => $order->ordered_at, 'desc' => 'Đơn hàng đã được khởi tạo'],
            ['time' => $order->updated_at, 'desc' => 'Trạng thái hiện tại: ' . $order->status]
        ];

        return response()->json([
            'status' => true, 
            'data' => $timeline
        ]);
    }

    // 📌 API 46: Xác nhận nhận hàng (PUT /api/orders/{id}/confirm)
public function confirmReceived($id) 
{
    // Tìm đơn hàng khớp với ID truyền vào và phải thuộc về User đang đăng nhập
    $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

    // 1. 🔥 ĐÃ FIX LỖI CRASH: Kiểm tra null trước khi gọi bất cứ thuộc tính nào của $order
    if (!$order) {
        return response()->json([
            'status'  => false,
            'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập!'
        ], 404);
    }

    // 2. CHẶN LOGIC SAI: Chỉ cho phép "Đã nhận hàng" nếu trạng thái hiện tại đang là "Đang giao" (shipping)
    if ($order->status !== 'shipping') {
        return response()->json([
            'status'  => false,
            'message' => 'Đơn hàng hiện chưa được giao, không thể xác nhận lúc này!'
        ], 400);
    }

    // 3. CẬP NHẬT TRẠNG THÁI
    $order->status = 'delivered'; // Hoặc 'completed' tùy vào quy định trạng thái cuối cùng trong database của bạn
    $order->save();

    // 4. (Tùy chọn nâng cao) Ghi nhận mốc thời gian hoàn thành đơn hàng nếu bảng orders có cột completed_at
    // $order->completed_at = now();
    // $order->save();

    return response()->json([
        'status'  => true,
        'message' => 'Xác nhận nhận hàng thành công! Cảm ơn bạn đã mua các sản phẩm UAV của chúng tôi.'
    ]);
}

    // 📌 API 47: Gửi yêu cầu hoàn tiền (POST /api/orders/{id}/refund)
public function requestRefund(Request $request, $id)
{
    // 1. Validate lý do hoàn tiền truyền lên từ phía người dùng
    $request->validate([
        'reason' => 'required|string|max:500',
    ], [
        'reason.required' => 'Vui lòng nhập lý do bạn muốn hoàn tiền.',
        'reason.max'      => 'Lý do hoàn tiền không được vượt quá 500 ký tự.'
    ]);

    // Tìm đơn hàng thuộc sở hữu của User đang đăng nhập
    $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

    // 2. 🔥 ĐÃ FIX: Kiểm tra đơn hàng null để tránh Fatal Error sập hệ thống (Lỗi 500)
    if (!$order) {
        return response()->json([
            'status'  => false,
            'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền yêu cầu hoàn tiền cho đơn này!'
        ], 404);
    }

    // 3. 🔥 ĐÃ FIX: Kiểm tra điều kiện trạng thái hợp lệ
    // Chỉ cho phép hoàn tiền khi đơn hàng đang giao (shipping) hoặc đã giao thành công (delivered)
    if (!in_array($order->status, ['shipping', 'delivered'])) {
        return response()->json([
            'status'  => false,
            'message' => 'Đơn hàng này chưa được xử lý hoặc giao đi, không thể yêu cầu hoàn tiền lúc này.'
        ], 400);
    }

    // 4. CHẶN LOGIC NÂNG CAO: Kiểm tra xem đơn hàng này đã từng gửi yêu cầu hoàn tiền trước đó chưa
    // Tránh việc user bấm gửi liên tục tạo ra nhiều bản ghi rác trong bảng refunds
    $alreadyRequested = DB::table('refunds')->where('order_id', $order->id)->exists();
    if ($alreadyRequested) {
        return response()->json([
            'status'  => false,
            'message' => 'Yêu cầu hoàn tiền cho đơn hàng này đã được gửi trước đó và đang chờ xử lý!'
        ], 400);
    }

    // 5. TIẾN HÀNH TRANSACTION ĐỂ ĐỒNG BỘ DỮ LIỆU AN TOÀN
    DB::beginTransaction();
    try {
        // Chèn bản ghi yêu cầu hoàn tiền vào bảng refunds
        DB::table('refunds')->insert([
            'order_id'      => $order->id,
            'user_id'       => Auth::id(),
            'refund_amount' => $order->total, // An toàn tuyệt đối vì $order đã được check null ở trên
            'reason'        => $request->reason,
            'status'        => 'pending',     // Trạng thái yêu cầu: Chờ Admin kiểm duyệt và phê duyệt hoàn tiền
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        // Cập nhật trạng thái đơn hàng (Tùy chọn: đổi sang trạng thái chờ hoàn tiền để Admin dễ quản lý)
        // $order->status = 'refund_pending';
        // $order->save();

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Gửi yêu cầu hoàn tiền thành công. Vui lòng chờ Ban quản trị duyệt kiểm tra sản phẩm UAV!'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Có lỗi hệ thống xảy ra khi gửi yêu cầu hoàn tiền!',
            'error'   => $e->getMessage()
        ], 500);
    }
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

    // 📌 API 42: Sửa ghi chú đơn hàng (PUT /api/orders/{id})
public function editNote(Request $request, $id) 
{
    // 1. Validate dữ liệu đầu vào
    $request->validate([
        'note' => 'nullable|string|max:500' // Cho phép trống hoặc tối đa 500 ký tự
    ], [
        'note.max' => 'Ghi chú không được vượt quá 500 ký tự.'
    ]);

    // 2. Lấy đơn hàng và kiểm tra quyền sở hữu
    $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

    // 3. Xử lý lỗi Crash: Kiểm tra đơn hàng tồn tại
    if (!$order) {
        return response()->json([
            'status'  => false,
            'message' => 'Không tìm thấy đơn hàng!'
        ], 404);
    }

    // 4. Chặn logic: Chỉ cho phép sửa ghi chú khi đơn hàng chưa xuất kho
    if (!in_array($order->status, ['pending', 'processing'])) {
        return response()->json([
            'status'  => false,
            'message' => 'Không thể sửa ghi chú vì đơn hàng đã được giao đi hoặc đã hủy!'
        ], 400);
    }

    // 5. 🔥 ĐÃ FIX: Gán giá trị và lưu vào database
    $order->note = $request->note;
    $order->save();

    return response()->json([
        'status'  => true,
        'message' => 'Đã cập nhật ghi chú đơn hàng thành công!',
        'data'    => [
            'order_id' => $order->id,
            'note'     => $order->note
        ]
    ]);
}
}