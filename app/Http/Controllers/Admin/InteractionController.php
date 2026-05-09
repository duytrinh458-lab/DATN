<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
    /* =========================
        CREATE COMMENT
    ========================= */
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // 🔥 CHECK ĐÃ REVIEW CHƯA
        $existing = DB::table('reviews')
            ->where('user_id', auth()->id())
            ->where('product_id', $id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Không thể đánh giá lại trên cùng 1 sản phẩm');
        }

        // 🔥 CHECK ĐÃ MUA CHƯA
        $hasBought = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', auth()->id())
            ->where('order_items.product_id', $id)
            ->exists();

        if (!$hasBought) {
            return back()->with('error', 'Bạn phải mua sản phẩm này trước khi đánh giá');
        }

        // 🔥 LẤY ORDER GẦN NHẤT
        $orderId = DB::table('orders')
            ->where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->value('id');

        // INSERT
        DB::table('reviews')->insert([
            'user_id' => auth()->id(),
            'product_id' => $id,
            'order_id' => $orderId,
            'rating' => $request->rating ?? 5,
            'comment' => $request->comment,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Đã gửi đánh giá');
    }

    /* =========================
        EDIT COMMENT
    ========================= */
    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update([
                'comment' => $request->comment,
                'rating' => $request->rating ?? 5,
            ]);

        return back()->with('success', 'Đã cập nhật đánh giá');
    }

    /* =========================
        DELETE COMMENT
    ========================= */
    public function deleteComment($id)
    {
        DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Đã xóa đánh giá');
    }

    /* =========================
        ADMIN VIEW
    ========================= */
   public function comments()
{
    $comments = DB::table('reviews')
        ->join('users', 'users.id', '=', 'reviews.user_id')
        ->join('products', 'products.id', '=', 'reviews.product_id')
        ->select(
            'reviews.id',
            'reviews.comment',
            'reviews.rating',
            'reviews.created_at',
            'users.full_name',
            'products.name as product_name'
        )
        ->orderBy('reviews.id', 'desc')
        ->get();

    return view('admin.interactions.comments', compact('comments'));
}
    public function likes()
    {
        return view('admin.interactions.likes');
    }

    public function ratings()
    {
        return view('admin.interactions.ratings');
    }
}