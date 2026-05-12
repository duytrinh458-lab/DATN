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
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $userId = auth()->id();

        $hasBought = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $id)
            ->exists();

        if (!$hasBought) {
            return back()->with('error', 'Bạn phải mua sản phẩm trước khi đánh giá');
        }

        $existing = DB::table('reviews')
            ->where('user_id', $userId)
            ->where('product_id', $id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi');
        }

        DB::table('reviews')->insert([
            'user_id'    => $userId,
            'product_id' => $id,
            'order_id'   => DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.user_id', $userId)
                ->where('order_items.product_id', $id)
                ->orderBy('orders.id', 'desc')
                ->value('orders.id'),

            'rating'     => $request->rating ?? 5,
            'comment'    => $request->comment,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Đã gửi đánh giá');
    }

    /* =========================
        UPDATE COMMENT (AJAX)
    ========================= */
    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $updated = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update([
                'comment' => $request->comment,
            ]);

        return response()->json([
            'success' => $updated ? true : false,
        ]);
    }

    /* =========================
        DELETE COMMENT (AJAX + WEB)
    ========================= */
    public function deleteComment($id)
    {
        $deleted = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $deleted ? true : false,
            ]);
        }

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
                'reviews.*',
                'users.full_name',
                'users.avatar',
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