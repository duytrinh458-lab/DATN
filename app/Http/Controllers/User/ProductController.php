<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ================= DANH SÁCH =================
    public function products(Request $request)
    {
        $query = Product::with('images');

        // Tìm kiếm theo tên sản phẩm
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->get();

        return view('User.products.products', compact('products'));
    }

    // ================= CHI TIẾT =================
    public function show($id)
    {
        $product = Product::with(['images', 'category'])->findOrFail($id);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        return view('User.products.product_detail', compact(
            'product',
            'relatedProducts'
        ));
    }

    // ================= THÊM COMMENT =================
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        Product::findOrFail($id);

        Review::create([
            'user_id'    => auth()->id(),
            'product_id' => $id,
            'comment'    => $request->comment,
            'rating'     => 5
        ]);

        return back()->with('success', 'Đã gửi bình luận');
    }

    // ================= UPDATE COMMENT =================
    public function updateComment(Request $request, $id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false
            ]);
        }

        $review->comment = $request->comment;
        $review->save();

        return response()->json([
            'success' => true
        ]);
    }

    // ================= DELETE COMMENT =================
    public function deleteComment($id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($review) {
            $review->delete();
        }

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

        $products = Product::with('images')
            ->where('category_id', $id)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        return view('User.categories.show', compact(
            'category',
            'products'
        ));
    }
}