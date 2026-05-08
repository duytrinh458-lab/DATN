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
    public function setComment(Request $request, $id)
    {
        $request->validate(['comment' => 'required']);

        DB::table('reviews')->insert([
            'user_id' => Auth::id(),
            'product_id' => $id,
            'order_id' => 1,
            'rating' => $request->rating ?? 5,
            'comment' => $request->comment,
            'created_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã gửi đánh giá UAV thành công'
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
}