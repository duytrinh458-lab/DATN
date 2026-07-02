<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // =========================================================================
    // 1. GIAO DIỆN DANH SÁCH SẢN PHẨM & BỘ LỌC THÔNG MINH (PRODUCTS)
    // =========================================================================
    /**
     * Chức năng: Hiển thị danh sách thiết bị UAV, xử lý tìm kiếm tách từ, 
     * lưu lịch sử tìm kiếm chống trùng, lọc theo danh mục, khoảng giá và sắp xếp.
     */
    public function products(Request $request)
    {
        // Điều kiện gốc: Sử dụng Eager Loading (with) để nạp trước ảnh và danh mục, tránh lỗi N+1. Chỉ lấy sản phẩm đang kinh doanh (active).
        $query = Product::with(['images', 'category'])->where('status', 'active');

        // --- PHÂN HỆ: TÌM KIẾM TÁCH TỪ & LƯU LỊCH SỬ THÔNG MINH ---
        if ($request->filled('search')) {
            $keyword = trim($request->search);

            // Bẫy lưu lịch sử: Nếu người dùng đã đăng nhập hệ thống
            if (Auth::check()) {
                // Kiểm tra phòng ngừa: Trong vòng 5 phút qua, người dùng này đã gõ từ khóa này chưa? (Tránh spam rác Database)
                $exists = DB::table('search_histories')
                    ->where('user_id', Auth::id())
                    ->where('keyword', $keyword)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->exists();

                // Nếu là từ khóa mới hoàn toàn trong 5 phút qua, tiến hành ghi vết vào bảng lịch sử để phân tích hành vi (Big Data)
                if (!$exists) {
                    DB::table('search_histories')->insert([
                        'user_id'    => Auth::id(),
                        'keyword'    => $keyword,
                        'created_at' => now()
                    ]);
                }
            }

            // Thuật toán tách từ khóa: Bẻ chuỗi tìm kiếm bằng khoảng trắng để tìm kiếm chính xác theo cụm từ đơn lẻ
            $words = preg_split('/\s+/', $keyword);
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    if (empty($word)) continue;
                    // Quét sâu: Từ khóa phải khớp một trong các trường: Tên UAV, Mã SKU, hoặc Mô tả sản phẩm
                    $q->where(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%{$word}%")
                            ->orWhere('sku', 'like', "%{$word}%")
                            ->orWhere('description', 'like', "%{$word}%");
                    });
                }
            });
        }

        // --- PHÂN HỆ: BỘ LỌC SẢN PHẨM THEO TIÊU CHÍ ---
        // Lọc sản phẩm theo danh mục (Category)
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Lọc sản phẩm theo ngưỡng giá tối đa do người dùng kéo thanh trượt
        if ($request->filled('price_max')) {
            $query->where('sale_price', '<=', (int) $request->price_max);
        }

        // --- PHÂN HỆ: ĐIỀU HƯỚNG SẮP XẾP THỨ TỰ (SORT) ---
        switch ($request->sort) {
            case 'price_asc':  // Giá tăng dần
                $query->orderBy('sale_price', 'asc');
                break;
            case 'price_desc': // Giá giảm dần
                $query->orderBy('sale_price', 'desc');
                break;
            case 'newest':     // Hàng mới về lên đầu
                $query->latest();
                break;
            default:           // Mặc định hiển thị hàng mới nhất
                $query->latest();
                break;
        }

        // Thực thi phân trang (Mỗi trang 8 sản phẩm để đảm bảo tốc độ tải và bố cục UI cân đối)
        $products   = $query->paginate(8);
        // Lấy danh sách toàn bộ danh mục để đổ vào thanh Sidebar làm bộ lọc cho khách chọn
        $categories = Category::orderBy('name')->get(); 

        return view('User.products.products', compact('products', 'categories'));
    }

    // =========================================================================
    // 2. GIAO DIỆN CHI TIẾT SẢN PHẨM (SHOW)
    // =========================================================================
    /**
     * Chức năng: Hiển thị thông tin chi tiết một cấu hình UAV, tự động tăng 
     * lượt xem phục vụ thuật toán định vị sản phẩm HOT, hiển thị sản phẩm liên quan cùng nhóm.
     */
    public function show($id)
    {
        // Tìm kiếm sản phẩm chính chủ, nếu sai ID hoặc hàng bị ẩn lập tức kích hoạt trang lỗi 404 bảo mật
        $product = Product::with(['images', 'category'])->findOrFail($id);

        // Tự động tăng biến đếm lượt xem/tìm kiếm lên +1 đơn vị mỗi khi có lượt truy cập xem chi tiết
        $product->increment('search_count');

        // Thuật toán gợi ý: Tìm 4 thiết bị UAV có cùng danh mục (category_id), đang mở bán, ngoại trừ chính nó
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        return view('User.products.product_detail', compact('product', 'relatedProducts'));
    }

    // =========================================================================
    // 3. QUẢN LÝ ĐÁNH GIÁ & BÌNH LUẬN (COMMENT / REVIEW)
    // =========================================================================
    /**
     * Chức năng: Cho phép người dùng để lại bình luận và số sao đánh giá.
     * Cơ chế phòng thủ nghiêm ngặt: Bắt buộc phải là khách đã mua và nhận hàng thành công mới được đánh giá.
     */
    public function storeComment(Request $request, $id)
    {
        // Kiểm tra dữ liệu đầu vào: Nội dung tối đa 1000 ký tự, số sao từ 1 đến 5
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $userId = Auth::id();

        // [XÁC THỰC NGHIỆP VỤ MUA HÀNG]: Kiểm tra xem user này đã từng mua UAV này và đơn hàng đã ở trạng thái "Đã giao" (delivered) chưa?
        $order = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $id)
            ->where('orders.status', 'delivered')
            ->select('orders.id')
            ->orderByDesc('orders.id')
            ->first();

        // Nếu không tìm thấy hóa đơn nào hợp lệ, chặn đứng hành vi "đánh giá ảo / seeding bẩn"
        if (!$order) {
            return back()->with('error', 'Bạn phải mua và nhận hàng trước khi đánh giá!');
        }

        // [CHỐNG ĐÁNH GIÁ TRÙNG LẶP]: Kiểm tra xem ứng với hóa đơn này, khách đã để lại đánh giá nào chưa?
        $exists = Review::where('user_id', $userId)
            ->where('product_id', $id)
            ->where('order_id', $order->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi!');
        }

        // Nếu vượt qua mọi màng lọc bảo mật, tiến hành lưu thông tin đánh giá chính thống vào hệ thống
        Review::create([
            'user_id'     => $userId,
            'product_id'  => $id,
            'order_id'    => $order->id,
            'comment'     => $request->comment,
            'rating'      => $request->rating ?? 5, // Nếu khách không chọn sao, mặc định chấm 5 sao chất lượng
            'is_approved' => 1, // Vì là tài khoản mua hàng thật, hệ thống tự động phê duyệt hiển thị công khai luôn
        ]);

        return back()->with('success', 'Đã gửi bình luận');
    }

    /**
     * Chức năng: Cập nhật nội dung bình luận qua phương thức gọi ngầm API Async (Ajax)
     */
    public function updateComment(Request $request, $id)
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        
        // Chỉ cho phép chính chủ nhân của bình luận đó sửa nội dung của họ
        $review = Review::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bình luận']);
        }

        $review->comment = $request->input('comment');
        $review->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    }

    /**
     * Chức năng: Xóa bình luận của chính mình khỏi hệ thống.
     */
    public function deleteComment($id)
    {
        // Bộ lọc an toàn: Tìm đúng ID bình luận và phải thuộc quyền sở hữu của tài khoản đang đăng nhập
        $review = Review::where('id', $id)->where('user_id', Auth::id())->first();
        
        if ($review) { 
            $review->delete(); 
        }
        
        return back()->with('success', 'Đã xóa bình luận');
    }
}