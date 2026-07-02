<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller quản lý toàn bộ hệ thống Đơn hàng (Orders) của website bán UAV/Linh kiện.
 * Thực hiện các nghiệp vụ: Hiển thị danh sách kết hợp phân trang, truy vấn chi tiết sản phẩm trong đơn hàng
 * và kiểm soát nghiêm ngặt vòng đời trạng thái đơn hàng (ngăn chặn cập nhật sai luồng logic).
 */
class OrderController extends Controller
{
    // ==========================================
    // 1. TRANG DANH SÁCH ĐƠN HÀNG (INDEX)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: index()
     * Lấy danh sách toàn bộ đơn hàng trong hệ thống kết hợp liên kết thông tin khách mua hàng.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | TRUY VẤN LIÊN KẾT BẢNG & PHÂN TRANG
        |--------------------------------------------------------------------------
        | - join('users', ...): Liên kết sang bảng người dùng để lấy thông tin định danh khách hàng.
        | - select(...): Lấy toàn bộ thuộc tính của đơn hàng kết hợp họ tên, số điện thoại và ảnh đại diện khách mua.
        | - orderBy('orders.id', 'desc'): Đơn hàng mới phát sinh sẽ luôn được xếp lên đầu danh sách.
        | - paginate(5): Phân trang cố định 5 mục trên một trang để đảm bảo giao diện scannable.
        */
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.*',
                'users.full_name',
                'users.phone',
                'users.avatar as user_avatar'
            )
            ->orderBy('orders.id', 'desc')
            ->paginate(5);

        // Trả về view danh sách đơn hàng của Admin và truyền biến $orders sang giao diện
        return view(
            'admin.orders.index',
            compact('orders')
        );
    }

    // ==========================================
    // 2. XEM CHI TIẾT ĐƠN HÀNG (SHOW)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: show()
     * Lấy ra thông tin tổng quan của đơn hàng và danh sách các sản phẩm/linh kiện nằm trong đơn hàng đó theo ID.
     */
    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | BƯỚC 1: LẤY THÔNG TIN TỔNG QUAN ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */
        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.*',
                'users.full_name',
                'users.phone',
                'users.avatar as user_avatar'
            )
            ->where('orders.id', $id)
            ->first();

        // Kiểm tra an toàn: Nếu nhập ID bậy bạ trên URL không có trong DB thì điều hướng về trang danh sách kèm lỗi
        if (!$order) {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Đơn hàng không tồn tại!');
        }

        /*
        |--------------------------------------------------------------------------
        | BƯỚC 2: LẤY DANH SÁCH SẢN PHẨM MUA KÈM (ORDER ITEMS)
        |--------------------------------------------------------------------------
        | Kết nối bảng trung gian 'order_items' với bảng 'products' để lấy tên mặt hàng,
        | số lượng mua, đơn giá thời điểm đó và tổng tiền của từng dòng sản phẩm.
        */
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

        // Trả về view chi tiết đơn hàng, truyền song song thông tin đơn hàng ($order) và danh sách sản phẩm ($items)
        return view(
            'admin.orders.show',
            compact('order', 'items')
        );
    }

    // ==========================================
    // 3. CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG (UPDATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: update()
     * Tiếp nhận trạng thái mới từ Admin, thực hiện kiểm tra logic nghiệp vụ nghiêm ngặt trước khi cập nhật.
     */
    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | XÁC THỰC DỮ LIỆU ĐẦU VÀO
        |--------------------------------------------------------------------------
        | Trạng thái gửi lên bắt buộc phải nằm trong 5 trạng thái vòng đời chuẩn:
        | pending (chờ xử lý), processing (đang xử lý), shipping (đang giao), delivered (đã giao), cancelled (đã hủy).
        */
        $request->validate([
            'status' => [
                'required',
                'in:pending,processing,shipping,delivered,cancelled'
            ]
        ]);

        // Lấy thông tin đơn hàng hiện tại trong Database để đối chiếu trạng thái cũ
        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {
            return back()->with('error', 'Đơn hàng không tồn tại!');
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI RÀO CHẮN LOGIC NGHIỆP VỤ (BUSINESS LOGIC GUARD)
        |--------------------------------------------------------------------------
        | Nguyên tắc tối quan trọng của TMĐT: Một khi đơn hàng đã chuyển sang trạng thái cuối cùng
        | là "Đã giao thành công" (delivered) hoặc "Đã bị hủy" (cancelled) thì KHÔNG ĐƯỢC phép quay 
        | ngược dòng trạng thái hay sửa đổi gì nữa để tránh sai lệch dữ liệu kế toán/kho bãi.
        */
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return back()->with(
                'error',
                'Không thể thay đổi trạng thái của đơn hàng đã giao hoặc đã hủy!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TIẾN HÀNH CẬP NHẬT TRẠNG THÁI MỚI
        |--------------------------------------------------------------------------
        */
        DB::table('orders')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => now() // Cập nhật mốc thời gian sửa đổi đơn hàng
            ]);

        // Quay lại trang trước đó và phát ra thông báo thành công cho Admin
        return back()->with(
            'success',
            'Cập nhật trạng thái đơn hàng thành công'
        );
    }
}