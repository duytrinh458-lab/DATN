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
    // ================= DANH SÁCH =================
public function products(Request $request)
{
    $query = Product::with([
        'images',
        'category'
    ]);

    if ($request->filled('search')) {

        $keyword = trim($request->search);

        // =========================
        // LƯU LỊCH SỬ TÌM KIẾM
        // =========================
        if (Auth::check()) {

            $exists = DB::table('search_histories')
                ->where('user_id', Auth::id())
                ->where('keyword', $keyword)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$exists) {

                DB::table('search_histories')->insert([
                    'user_id'    => Auth::id(),
                    'keyword'    => $keyword,
                    'created_at' => now()
                ]);
            }
        }

        // =========================
        // SEARCH THÔNG MINH
        // =========================
        $words = preg_split('/\s+/', $keyword);

        $query->where(function ($q) use ($words) {

            foreach ($words as $word) {

                if (empty($word)) {
                    continue;
                }

                $q->where(function ($sub) use ($word) {

                    $sub->where('name', 'like', "%{$word}%")
                        ->orWhere('sku', 'like', "%{$word}%")
                        ->orWhere('description', 'like', "%{$word}%");

                });
            }
        });
    }

    $products = $query
        ->where('status', 'active')
        ->latest()
        ->paginate(8);

    return view(
        'User.products.products',
        compact('products')
    );
}

    // ================= CHI TIẾT =================
    public function show($id)
{
    $product = Product::with([
        'images',
        'category'
    ])->findOrFail($id);

    // TĂNG LƯỢT QUAN TÂM
    $product->increment('search_count');

    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $id)
        ->where('status', 'active')
        ->limit(4)
        ->get();

    return view(
        'User.products.product_detail',
        compact(
            'product',
            'relatedProducts'
        )
    );
}

    // ================= THÊM COMMENT =================
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $userId = Auth::id();

        $order = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $id)
            ->where('orders.status', 'delivered')
            ->select('orders.id')
            ->orderByDesc('orders.id')
            ->first();

        if (!$order) {
            return back()->with('error', 'Bạn phải mua và nhận hàng trước khi đánh giá!');
        }

        $exists = Review::where('user_id', $userId)
            ->where('product_id', $id)
            ->where('order_id', $order->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi!');
        }

        Review::create([
            'user_id'     => $userId,
            'product_id'  => $id,
            'order_id'    => $order->id,
            'comment'     => $request->comment,
            'rating'      => $request->rating ?? 5,
            'is_approved' => 1,
        ]);

        return back()->with('success', 'Đã gửi bình luận');
    }

    public function updateComment(Request $request, $id)
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        $review = Review::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bình luận']);
        }

        $review->comment = $request->input('comment');
        $review->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    }

    public function deleteComment($id)
    {
        $review = Review::where('id', $id)->where('user_id', Auth::id())->first();
        if ($review) { $review->delete(); }
        return back()->with('success', 'Đã xóa bình luận');
    }

    // ================= DANH MỤC =================
    public function categories()
    {
        $categories = Category::orderBy('id', 'desc')->get();
        return view('User.categories.index', compact('categories'));
    }

    public function byCategory($id)
    {
        $category = Category::findOrFail($id);

        // ĐÃ SỬA: Phân trang 10 SP/trang cho danh mục
        $products = Product::with('images')
            ->where('category_id', $id)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->paginate(8);

        return view('User.categories.show', compact('category', 'products'));
    }
}