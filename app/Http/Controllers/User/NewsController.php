<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // ================= 1. TRANG DANH SÁCH TIN TỨC & TÌM KIẾM NÂNG CAO =================
    /**
     * Chức năng: Hiển thị danh sách tin tức công nghệ, hướng dẫn bay và xử lý bộ lọc tìm kiếm từ khóa thông minh.
     */
    public function index(Request $request)
    {
        // Điều kiện gốc: Chỉ lấy các bài viết đã được biên tập và phê duyệt hiển thị (published).
        $query = News::where('status', 'published');

        // --- THUẬT TOÁN TÌM KIẾM THÔNG MINH (Chỉ chạy khi khách nhập ô tìm kiếm) ---
        if ($request->filled('search')) {
            $search = trim($request->search);

            // Tách chuỗi tìm kiếm thành mảng các từ khóa độc lập dựa trên khoảng trắng hoặc dấu gạch ngang (-)
            // Mục đích: Khách gõ "máy-bay dji" thì hệ thống vẫn nhận diện được hai từ "máy", "bay", "dji" để tìm kiếm chính xác.
            $keywords = preg_split('/[\s\-]+/', strtolower($search));

            $query->where(function ($q) use ($search, $keywords) {
                // Ưu tiên 1: Nếu khách gõ vào là một dãy số, hệ thống sẽ tự hiểu là đang tìm theo mã số (ID) bài viết.
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }

                // Ưu tiên 2: Thuật toán quét đa tầng (Mỗi từ khóa khách gõ đều phải xuất hiện trong bài viết)
                foreach ($keywords as $word) {
                    if (empty($word)) {
                        continue;
                    }

                    // Với từng từ khóa, hệ thống sẽ lục lọi đồng thời trong cả Tiêu đề (Title), Đường dẫn định danh (Slug), và Nội dung chi tiết (Content)
                    // Hàm LOWER() giúp tìm kiếm không phân biệt chữ hoa hay chữ thường (Ví dụ: "UAV", "uav", "Uav" đều ra kết quả).
                    $q->where(function ($sub) use ($word) {
                        $sub->whereRaw('LOWER(title) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(content) LIKE ?', ["%{$word}%"]);
                    });
                }
            });
        }

        // Thực hiện phân trang: Mỗi trang giao diện hiển thị tối đa 8 bài viết mới nhất (latest).
        // Hàm appends() giúp giữ lại từ khóa tìm kiếm trên thanh địa chỉ khi khách bấm chuyển sang trang 2, trang 3...
        $news = $query->latest()
            ->paginate(8)
            ->appends($request->query());

        // Đổ dữ liệu ra giao diện danh sách tin tức cho người dùng trải nghiệm.
        return view('User.news.index', compact('news'));
    }

    // ================= 2. TRANG CHI TIẾT BÀI VIẾT =================
    /**
     * Chức năng: Hiển thị nội dung đầy đủ của một bài viết cụ thể khi khách hàng bấm vào xem.
     */
    public function show($id)
    {
        // Tìm đúng bài viết theo mã ID, bắt buộc bài viết đó phải ở trạng thái công khai (published).
        // Nếu bài viết không tồn tại hoặc đang ở dạng bản nháp (draft), hệ thống tự động trả về trang lỗi 404 để bảo mật.
        $news = News::where('status', 'published')->findOrFail($id);

        // Trả về giao diện chi tiết bài viết (Đọc tin tức, hướng dẫn kỹ thuật...).
        return view('User.news.show', compact('news'));
    }
}