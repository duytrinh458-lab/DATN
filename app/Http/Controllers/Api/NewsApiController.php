<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ các API liên quan đến hệ thống tin tức và bài viết công khai trên ứng dụng/website.
 * Giúp người dùng có thể cập nhật các thông báo, tin tức mới nhất từ hệ thống.
 */

/**
 * CHỨC NĂNG CLASS:
 * Sử dụng Laravel Query Builder (DB Facade) để kết nối trực tiếp đến bảng 'news' trong cơ sở dữ liệu.
 * Tiến hành lọc trạng thái và sắp xếp dữ liệu bài viết trước khi trả về định dạng JSON cho phía Frontend hiển thị.
 */
class NewsApiController extends Controller
{
    // 📌 API 52: Xem danh sách tin tức (GET /api/get_list_news)
    // VAI TRÒ: Lấy toàn bộ danh sách các bài viết tin tức hợp lệ trong hệ thống và sắp xếp theo thứ tự bài mới nhất lên đầu.
    public function index()
    {
        // TRUY VẤN: Kết nối đến bảng 'news' để lấy dữ liệu.
        // 1. Điều kiện lọc: Chỉ lấy những tin tức có trạng thái ('status') là 'published' (đã xuất bản công khai), ẩn các bài nháp hoặc bài đã bị hạ xuống.
        // 2. Sắp xếp: Sử dụng `orderBy` theo trường 'published_at' với thứ tự giảm dần ('desc') để đảm bảo các bài viết vừa đăng sẽ nằm ở vị trí đầu tiên.
        $news = DB::table('news')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Lấy danh sách tin tức hạm đội thành công',
            'data'    => $news // Trả về mảng danh sách bài viết (hoặc mảng rỗng [] nếu chưa có bài nào)
        ]);
    }

    // 📌 API 53: Chi tiết bản tin (GET /api/get_news/{id})
    // VAI TRÒ: Truy xuất thông tin đầy đủ và chi tiết của một bài viết cụ thể khi người dùng bấm vào xem.
    public function show($id)
    {
        // TRUY VẤN: Tìm kiếm bản ghi trong bảng 'news' dựa trên tham số định danh định vị ($id) nhận từ URL.
        // Hàm `first()` đảm bảo chỉ lấy ra đúng 1 đối tượng duy nhất thỏa mãn điều kiện.
        $article = DB::table('news')
            ->where('id', $id)
            ->first();

        // KHỐI LỆNH CHẶN LỖI: Kiểm tra xem bài viết có tồn tại thực tế hay không.
        // Nếu biến $article trả về giá trị rỗng (null) do ID không hợp lệ, hệ thống sẽ trả về lỗi mã 404 (Không tìm thấy tài nguyên).
        if (!$article) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy bài viết này'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $article // Trả về toàn bộ các trường thông tin chi tiết của bài viết đó (Tiêu đề, nội dung, ảnh đại diện...)
        ]);
    }
}