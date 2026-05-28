<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    // 📌 API 17: Xem tất cả UAV
    public function index()
    {
        $products = Product::with(['category', 'images'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    // 📌 API 18: Chi tiết 1 UAV
    public function show($id)
    {
        $product = Product::with(['category', 'images'])->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy hạm đội UAV này'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    // 📌 API 20: Tìm kiếm UAV + Lưu lịch sử
    public function search(Request $request)
    {
        $keyword = $request->q;

        $products = Product::with(['category', 'images'])
            ->where('name', 'like', "%$keyword%")
            ->get();

        if (Auth::check() && !empty($keyword)) {
            DB::table('search_histories')->insert([
                'user_id' => Auth::id(),
                'keyword' => $keyword,
                'created_at' => now()
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    // 📌 API 21: Lưu lịch sử tìm kiếm thủ công (POST /api/search/save)
    public function saveSearch(Request $request)
    {
        $request->validate(['keyword' => 'required']);

        if (Auth::check()) {
            DB::table('search_histories')->insert([
                'user_id' => Auth::id(),
                'keyword' => $request->keyword,
                'created_at' => now()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã lưu từ khóa tìm kiếm'
        ]);
    }

    // 📌 API 19: Hàng mới về
    public function checkNewItems()
    {
        $newProducts = Product::with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'status' => true,
            'data' => $newProducts
        ]);
    }

    // 📌 API 23: Xem Bình luận
    public function getComments($id)
    {
        $comments = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.product_id', $id)
            ->select('reviews.rating', 'reviews.comment', 'reviews.created_at', 'users.full_name', 'users.avatar')
            ->orderBy('reviews.id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $comments
        ]);
    }

    // 📌 API 24: Viết Bình luận
    // 📌 API: Viết bình luận/đánh giá sản phẩm (POST /api/products/{id}/comment)
public function setComment(Request $request, $id)
{
    // 1. Validate dữ liệu truyền lên, bắt buộc phải truyền order_id hợp lệ
    $request->validate([
        'comment'  => 'required|string|max:1000',
        'rating'   => 'required|integer|min:1|max:5',
        'order_id' => 'required|exists:orders,id', // Kiểm tra order_id phải tồn tại trong bảng orders
    ], [
        'comment.required'  => 'Vui lòng nhập nội dung bình luận.',
        'rating.required'   => 'Vui lòng chọn số sao đánh giá.',
        'order_id.required' => 'Thiếu mã đơn hàng để đánh giá.',
        'order_id.exists'   => 'Đơn hàng không tồn tại trên hệ thống.',
    ]);

    $userId = Auth::id();

    // 2. ĐỐI CHIẾU BẢO MẬT: Kiểm tra xem đơn hàng này có đúng là của User này hay không
    // Tránh việc User A truyền order_id của User B vào để phá hoại dữ liệu
    $orderExists = DB::table('orders')
        ->where('id', $request->order_id)
        ->where('user_id', $userId)
        ->exists();

    if (!$orderExists) {
        return response()->json([
            'status'  => false,
            'message' => 'Bạn không có quyền đánh giá cho đơn hàng này!'
        ], 403);
    }

    // 3. KIỂM TRA TRÙNG LẶP (Tùy chọn): Nếu một đơn hàng chỉ cho phép đánh giá sản phẩm này 1 lần
    $alreadyReviewed = DB::table('reviews')
        ->where('order_id', $request->order_id)
        ->where('product_id', $id)
        ->where('user_id', $userId)
        ->exists();

    if ($alreadyReviewed) {
        return response()->json([
            'status'  => false,
            'message' => 'Sản phẩm trong đơn hàng này đã được bạn đánh giá trước đó!'
        ], 400);
    }

    // 4. TIẾN HÀNH INSERT (Xóa bỏ hoàn toàn số 1 hardcode)
    DB::table('reviews')->insert([
        'user_id'    => $userId,
        'product_id' => $id,
        'order_id'   => $request->order_id, // 🔥 ĐÃ FIX: Lấy động theo đơn hàng thực tế truyền lên
        'comment'    => $request->comment,
        'rating'     => $request->rating,
        'parent_id'  => null, // Mặc định là đánh giá gốc, không phải reply
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 5. Trả về JSON Response chuẩn form các API khác của bạn
    return response()->json([
        'status'  => true,
        'message' => 'Gửi đánh giá sản phẩm thành công!'
    ]);
}

    // 📌 API 25: Thả tim / Bỏ tim
    public function likeProduct($id)
    {
        $userId = Auth::id();
        $liked = DB::table('product_likes')
            ->where('user_id', $userId)
            ->where('product_id', $id)
            ->first();

        if ($liked) {
            DB::table('product_likes')->where('id', $liked->id)->delete();
            return response()->json(['status' => true, 'message' => 'Đã bỏ yêu thích']);
        } else {
            DB::table('product_likes')->insert([
                'user_id' => $userId,
                'product_id' => $id,
                'created_at' => now()
            ]);
            return response()->json(['status' => true, 'message' => 'Đã thêm vào danh sách yêu thích']);
        }
    }

    // ==========================================================
    // NHÓM API QUẢN TRỊ (ADMIN)
    // ==========================================================

    // 📌 API 57: Thêm UAV mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'original_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'stock' => 'required|integer'
        ]);

        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'sku' => 'UAV-' . time(),
            'description' => $request->description,
            'original_price' => $request->original_price,
            'sale_price' => $request->sale_price,
            'stock' => $request->stock,
            'status' => 'active'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã nhập thêm UAV mới vào kho',
            'data' => $product
        ], 201);
    }

    // 📌 API 58: Sửa thông số UAV
    public function update(Request $request, $id)
    {
        /** @var \App\Models\Product $product */
        $product = Product::find($id);
        
        if (!$product) return response()->json(['status' => false, 'message' => 'Không tìm thấy UAV'], 404);

        $product->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông số UAV thành công',
            'data' => $product
        ]);
    }

    // 📌 API 59: Xóa UAV khỏi kho
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['status' => false, 'message' => 'Không tìm thấy UAV'], 404);

        $product->delete();
        return response()->json(['status' => true, 'message' => 'Đã loại bỏ UAV khỏi hệ thống']);
    }


    // 📌 API 15: Xem danh sách phân loại UAV
    public function getCategories()
    {
        $categories = DB::table('categories')->where('is_active', 1)->get();
        return response()->json(['status' => true, 'data' => $categories]);
    }

    // 📌 API 16: Danh sách thương hiệu (Hãng sản xuất)
    public function getBrands()
    {
        $brands = DB::table('brands')->get();
        return response()->json(['status' => true, 'data' => $brands]);
    }

    // 📌 API 22: Xem lịch sử tìm kiếm của user
    public function getSearchHistory()
    {
        $histories = DB::table('search_histories')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json(['status' => true, 'data' => $histories]);
    }
}