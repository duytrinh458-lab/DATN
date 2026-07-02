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
    /*
    |--------------------------------------------------------------------------
    | 1. DANH SÁCH ĐƠN HÀNG LỊCH SỬ (INDEX)
    |--------------------------------------------------------------------------
    | Chức năng: Lấy danh sách đơn hàng của tài khoản đang đăng nhập,
    | sắp xếp theo thời gian mua mới nhất và phân trang hiển thị.
    */
    public function index()
    {
        // Lấy các đơn hàng của user hiện tại, xếp đơn mới mua lên đầu và chia nhỏ 3 đơn mỗi trang (để tối ưu giao diện scannable)
        $orders = Order::where('user_id', Auth::id())
                       ->orderBy('ordered_at', 'desc')
                       ->paginate(3); 

        return view('User.orders.index', compact('orders'));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. CHI TIẾT ĐƠN HÀNG ĐÃ ĐẶT (SHOW)
    |--------------------------------------------------------------------------
    | Chức năng: Hiển thị tường tận một đơn hàng cụ thể bao gồm sản phẩm, hình ảnh,
    | địa chỉ nhận UAV và kiểm tra trạng thái khiếu nại/hoàn trả.
    */
    public function show($id)
    {
        // Nạp tối ưu dữ liệu (Eager Loading): Lấy Đơn hàng kèm theo Chi tiết đơn, thông tin UAV, hình ảnh và Sổ địa chỉ
        $order = Order::with(['orderItems.product.images', 'address'])
                      ->where('user_id', Auth::id())
                      ->findOrFail($id);

        // Kiểm tra an toàn: Xem đơn hàng này trong quá khứ đã từng gửi yêu cầu hoàn hàng/hoàn tiền chưa
        $hasRefundRequest = DB::table('refunds')->where('order_id', $id)->exists();

        // Truyền toàn bộ thông tin đơn hàng và trạng thái kiểm tra hoàn trả sang trang giao diện chi tiết
        return view('User.orders.show', compact('order', 'hasRefundRequest'));
    }

    /*
    |--------------------------------------------------------------------------
    | 3. HỦY ĐƠN HÀNG TỰ ĐỘNG (CANCEL)
    |--------------------------------------------------------------------------
    | Chức năng: Cho phép khách hàng tự hủy đơn khi hệ thống chưa xử lý (status = pending).
    | Cam kết an toàn dữ liệu: Tự động hoàn trả số lượng thiết bị UAV về kho tổng.
    */
    public function cancel(Request $request, $id)
    {
        // Xác thực quyền sở hữu đơn hàng của chính chủ
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // Chặn lỗi nghiệp vụ: Đơn đã đi hàng (hoặc đã giao thành công) thì không cho phép tự hủy tự do nữa
        if ($order->status !== 'pending') {
            return back()->with('error', 'CẢNH BÁO: Chỉ có thể hủy đơn hàng khi đang ở trạng thái Chờ duyệt!');
        }

        // [AN TOÀN HỆ THỐNG]: Khởi động Transaction để bảo vệ tính toàn vẹn giữa trạng thái đơn và số lượng kho thực tế
        DB::beginTransaction();
        try {
            // Cập nhật trạng thái đơn hàng sang "Đã hủy" (cancelled)
            $order->status = 'cancelled';
            $order->save();

            // Vòng lặp hoàn kho: Đọc từng thiết bị trong đơn hàng bị hủy và cộng trả lại số lượng vào kho tổng của hệ thống
            foreach ($order->orderItems as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }

            // Mọi thao tác hoàn hảo -> Xác nhận lưu thay đổi vĩnh viễn vào Database
            DB::commit();
            return back()->with('success', 'Đã hủy đơn hàng ' . $order->order_code . '. Các thiết bị UAV đã được trả về kho tổng.');
            
        } catch (\Exception $e) {
            // Gặp lỗi bất ngờ -> Khôi phục lại trạng thái ban đầu để tránh sai lệch dữ liệu kho hàng
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 4. GIAO DIỆN FORM ĐĂNG KÝ HOÀN TRẢ (SHOW REFUND FORM)
    |--------------------------------------------------------------------------
    | Chức năng: Hiển thị form điền lý do trả hàng lỗi cho khách.
    | Điều kiện bắt buộc: Đơn hàng phải giao thành công thì mới được phép khiếu nại.
    */
    public function showRefundForm($id)
    {
        $order = Order::where('user_id', Auth::id())
                      ->where('status', 'delivered') // Bộ lọc bảo mật: Đơn hàng phải ở trạng thái "Đã giao"
                      ->findOrFail($id);

        return view('User.orders.refund', compact('order'));
    }

    /*
    |--------------------------------------------------------------------------
    | 5. XỬ LÝ LƯU KHÁCH HÀNG KHIẾU NẠI/HOÀN HÀNG (SUBMIT REFUND)
    |--------------------------------------------------------------------------
    | Chức năng: Tiếp nhận lý do, mô tả lỗi kỹ thuật của UAV từ khách hàng,
    | kiểm tra chống spam gửi đơn liên tiếp và đưa vào hàng đợi chờ Admin xử lý.
    */
    public function submitRefund(Request $request, $id)
    {
        // Kiểm định dữ liệu đầu vào: Bắt buộc chọn nhóm lý do và có bài mô tả chi tiết lỗi thực tế
        $request->validate([
            'reason'      => 'required|string|max:255',
            'description' => 'required|string',
        ], [
            'reason.required'      => 'Vui lòng chọn lý do hoàn hàng.',
            'description.required' => 'Vui lòng mô tả chi tiết lỗi của UAV.',
        ]);

        // Phòng thủ dữ liệu: Đảm bảo tài khoản sở hữu hợp pháp đơn hàng đã giao thành công này
        $order = Order::where('user_id', Auth::id())
                      ->where('status', 'delivered')
                      ->findOrFail($id);

        // [CHỐNG SPAM]: Kiểm tra xem đơn hàng này đã tồn tại yêu cầu hoàn trả nào đang nằm trong hàng đợi chưa
        $exists = DB::table('refunds')->where('order_id', $order->id)->exists();
        if ($exists) {
            return redirect()->route('user.orders.index')->with('error', 'Yêu cầu hoàn trả cho đơn hàng này đã tồn tại và đang chờ xử lý.');
        }

        // Ghi nhận hồ sơ khiếu nại vào bảng dữ liệu refunds để kỹ thuật viên kiểm duyệt công khai
        DB::table('refunds')->insert([
            'order_id'    => $order->id,
            'user_id'     => Auth::id(),
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'pending', // Trạng thái mặc định: Chờ quản trị viên/Kỹ thuật viên thẩm định lỗi phần cứng UAV
            'created_at'  => now(),
        ]);

        return redirect()->route('user.orders.index')->with('success', 'Đã gửi yêu cầu hoàn trả. Kỹ thuật viên sẽ kiểm tra và liên hệ với bạn sớm nhất!');
    }
}