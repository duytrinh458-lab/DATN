<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MỤC ĐÍCH FILE:
 * File này chịu trách nhiệm xử lý các API liên quan đến hệ thống thông báo (Notifications) của người dùng trong ứng dụng.
 * Giúp người dùng theo dõi các cập nhật cá nhân và quản lý trạng thái đọc/chưa đọc của từng thông báo.
 */

/**
 * CHỨC NĂNG CLASS:
 * Sử dụng Laravel Query Builder (DB Facade) để tương tác trực tiếp với bảng dữ liệu 'notifications'.
 * Đảm bảo tính bảo mật và riêng tư bằng cách luôn ràng buộc dữ liệu theo ID của người dùng đang đăng nhập.
 */
class NotificationApiController extends Controller
{
    // 📌 API 54: Lấy danh sách thông báo của user (GET /api/notifications)
    // VAI TRÒ: Truy xuất toàn bộ danh sách thông báo cá nhân của người dùng và sắp xếp theo thứ tự thời gian mới nhất.
    public function index()
    {
        // BIẾN QUAN TRỌNG: Lấy ID của người dùng hiện tại từ Token xác thực nhằm cá nhân hóa dữ liệu và bảo mật.
        $userId = Auth::id();

        // TRUY VẤN: Kết nối đến bảng 'notifications' trong cơ sở dữ liệu để lấy dữ liệu.
        // 1. Điều kiện lọc: Chỉ lấy những thông báo có 'user_id' trùng với ID của người dùng đang thực hiện request.
        // 2. Sắp xếp: Sử dụng `orderBy` theo trường 'created_at' với thứ tự giảm dần ('desc') để đưa các thông báo vừa nhận lên vị trí đầu tiên.
        $notifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách thông báo thành công',
            'data' => $notifications // Trả về mảng danh sách các thông báo nhận được
        ]);
    }

    // 📌 API 55: Đánh dấu thông báo đã đọc (POST /api/notifications/read)
    // VAI TRÒ: Cập nhật trạng thái của một thông báo cụ thể từ "chưa đọc" sang "đã đọc" khi người dùng tương tác.
    public function markAsRead(Request $request)
    {
        // KHỐI LỆNH VALIDATE: Kiểm tra tính hợp lệ của dữ liệu gửi lên.
        // Trường 'noti_id' là bắt buộc (required) và bắt buộc phải tồn tại thực tế trong cột 'id' của bảng 'notifications' (exists:notifications,id) để tránh lỗi hệ thống.
        $request->validate([
            'noti_id' => 'required|exists:notifications,id'
        ]);

        $userId = Auth::id();

        // TRUY VẤN CẬP NHẬT: Tiến hành thay đổi trạng thái của thông báo trong database.
        // RÀNG BUỘC BẢO MẬT: Ngoài việc tìm theo 'id' của thông báo, bắt buộc phải check thêm điều kiện thuộc về 'user_id' của chính họ.
        // Điều này ngăn chặn lỗ hổng bảo mật IDOR (Insecure Direct Object Reference) — tránh việc user A cố tình truyền ID thông báo của user B để cập nhật bừa bãi.
        DB::table('notifications')
            ->where('id', $request->noti_id)
            ->where('user_id', $userId)
            ->update(['is_read' => 1]); // Cập nhật trạng thái cột 'is_read' thành 1 (Đã đọc)

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu thông báo đã đọc'
        ]);
    }
}