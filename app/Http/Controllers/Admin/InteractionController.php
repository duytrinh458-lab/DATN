<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Review;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller quản lý các tương tác của khách hàng trên hệ thống (Bình luận/Đánh giá, Lượt thích, Điểm sao).
 * Hỗ trợ các tác vụ tải dữ liệu liên kết (Join bảng), tự động cập nhật trạng thái thông báo và xóa bản ghi thông qua cả Request thông thường lẫn AJAX.
 */
class InteractionController extends Controller
{
    // ==========================================
    // 1. QUẢN LÝ BÌNH LUẬN / ĐÁNH GIÁ (COMMENTS)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: comments()
     * Đánh dấu tất cả bình luận mới là đã đọc, sau đó lấy danh sách bình luận kèm thông tin chi tiết của người dùng và sản phẩm.
     */
    public function comments()
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI ĐÁNH DẤU ĐÃ ĐỌC COMMENT MỚI
        |--------------------------------------------------------------------------
        | Mục đích: Tự động chuyển toàn bộ các bình luận đang ở trạng thái chưa đọc (is_read = 0)
        | thành đã đọc (is_read = 1) ngay khi Admin vừa truy cập vào trang này để làm mới thông báo.
        */
        Review::where('is_read', 0)->update([
            'is_read' => 1
        ]);

        /*
        |--------------------------------------------------------------------------
        | KHỐI TRUY VẤN LIÊN KẾT DỮ LIỆU (LOAD COMMENTS WITH JOIN)
        |--------------------------------------------------------------------------
        | Thực hiện kết nối bảng 'reviews' với 'users' và 'products' để lấy đầy đủ thông tin hiển thị.
        | - leftJoin('users', ...): Lấy thông tin người dùng (họ tên, avatar), nếu tài khoản bị xóa thì bình luận vẫn không mất.
        | - leftJoin('products', ...): Lấy tên sản phẩm được bình luận.
        | - select(...): Chỉ định rõ các cột cần lấy, dùng bí danh 'products.name as product_name' để tránh đè tên với bảng review.
        | - paginate(5): Phân trang, hiển thị cố định 5 mục trên mỗi trang để đảm bảo giao diện scannable.
        */
        $comments = DB::table('reviews')
            ->leftJoin('users', 'users.id', '=', 'reviews.user_id')
            ->leftJoin('products', 'products.id', '=', 'reviews.product_id')
            ->select(
                'reviews.*',
                'users.full_name',
                'users.avatar',
                'products.name as product_name'
            )
            ->orderByDesc('reviews.id')
            ->paginate(5);

        // Trả về view giao diện quản lý bình luận, truyền theo danh sách dữ liệu $comments
        return view('admin.interactions.comments', compact('comments'));
    }

    // ==========================================
    // 2. GIAO DIỆN LƯỢT THÍCH (LIKES)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: likes()
     * Hiển thị danh sách các lượt thích sản phẩm của người dùng trên hệ thống.
     */
    public function likes()
    {
        return view('admin.interactions.likes');
    }

    // ==========================================
    // 3. GIAO DIỆN XẾP HẠNG ĐÁNH GIÁ (RATINGS)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: ratings()
     * Hiển thị bảng thống kê chi tiết số sao (1-5 sao) do khách hàng chấm điểm cho các dòng UAV/Sản phẩm.
     */
    public function ratings()
    {
        return view('admin.interactions.ratings');
    }

    // ==========================================
    // 4. XỬ LÝ XÓA BÌNH LUẬN (DELETE COMMENT)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: deleteComment()
     * Tiếp nhận ID bình luận, tiến hành xóa khỏi DB và đưa ra phản hồi tương ứng theo kiểu Request (Giao diện cũ hoặc Gọi API ngầm).
     */
    public function deleteComment($id)
    {
        // Thực hiện xóa bản ghi trong bảng 'reviews' theo đúng ID được truyền vào
        // Biến $deleted sẽ nhận giá trị true (nếu xóa thành công) hoặc false (nếu không tìm thấy ID để xóa)
        $deleted = DB::table('reviews')
            ->where('id', $id)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | KHỐI PHẢN HỒI QUA AJAX (AJAX RESPONSE)
        |--------------------------------------------------------------------------
        | Kiểm tra xem yêu cầu xóa này được gọi bằng JavaScript/Axios/Fetch gửi lên ngầm hay không.
        | Nếu đúng, trả về chuỗi JSON chứa trạng thái kết quả để giao diện Front-end tự động xử lý (không cần tải lại trang).
        */
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $deleted ? true : false,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI PHẢN HỒI THÔNG THƯỜNG (NORMAL HTTP RESPONSE)
        |--------------------------------------------------------------------------
        | Trường hợp gọi xóa bằng thẻ liên kết <a> hoặc Form submit truyền thống.
        | Quay trở lại trang trước đó (back) và đẩy kèm thông báo thành công ra màn hình Flash Session.
        */
        return back()->with(
            'success',
            'Đã xóa bình luận thành công'
        );
    }
}