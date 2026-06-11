<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
                       ->paginate(3); 

        return view('User.orders.index', compact('orders'));
    }

    /**
     * 2. Xem chi tiết một đơn hàng cụ thể
     */
    public function show($id)
    {
        $order = Order::with(['orderItems.product.images', 'address'])
                      ->where('user_id', Auth::id())
                      ->findOrFail($id);

        // 💡 KIỂM TRA TỒN TẠI YÊU CẦU HOÀN TRẢ
        $hasRefundRequest = DB::table('refunds')->where('order_id', $id)->exists();

        // 💡 TRUYỀN THÊM BIẾN NÀY SANG VIEW
        return view('User.orders.show', compact('order', 'hasRefundRequest'));
    }

    /**
     * 3. Hủy đơn hàng (Trước khi giao - Chỉ khi status = pending)
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return back()->with('error', 'CẢNH BÁO: Chỉ có thể hủy đơn hàng khi đang ở trạng thái Chờ duyệt!');
        }

        DB::beginTransaction();
        try {
            $order->status = 'cancelled';
            $order->save();

            // Hoàn lại số lượng UAV về kho
            foreach ($order->orderItems as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }

            DB::commit();
            return back()->with('success', 'Đã hủy đơn hàng ' . $order->order_code . '. Các thiết bị UAV đã được trả về kho tổng.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * 4. Hiển thị Form đăng ký hoàn trả (Sau khi đã nhận hàng)
     */
    public function showRefundForm($id)
    {
        $order = Order::where('user_id', Auth::id())
                      ->where('status', 'delivered') // Chỉ cho phép nếu đã nhận hàng
                      ->findOrFail($id);

        return view('User.orders.refund', compact('order'));
    }

    /**
     * 5. Xử lý lưu yêu cầu hoàn hàng vào hệ thống
     */
    public function submitRefund(Request $request, $id)
    {
        $request->validate([
            'reason'      => 'required|string|max:255',
            'description' => 'required|string',
        ], [
            'reason.required'      => 'Vui lòng chọn lý do hoàn hàng.',
            'description.required' => 'Vui lòng mô tả chi tiết lỗi của UAV.',
        ]);

        $order = Order::where('user_id', Auth::id())
                      ->where('status', 'delivered')
                      ->findOrFail($id);

        // Kiểm tra xem đã gửi yêu cầu cho đơn này chưa (tránh spam gửi nhiều lần)
        $exists = DB::table('refunds')->where('order_id', $order->id)->exists();
        if ($exists) {
            return redirect()->route('user.orders.index')->with('error', 'Yêu cầu hoàn trả cho đơn hàng này đã tồn tại và đang chờ xử lý.');
        }

        // Lưu thông tin vào bảng refunds 
        DB::table('refunds')->insert([
            'order_id'    => $order->id,
            'user_id'     => Auth::id(),
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'pending', // Trạng thái chờ Admin thẩm định
            'created_at'  => now(),
        ]);

        return redirect()->route('user.orders.index')->with('success', 'Đã gửi yêu cầu hoàn trả. Kỹ thuật viên sẽ kiểm tra và liên hệ với bạn sớm nhất!');
    }
}