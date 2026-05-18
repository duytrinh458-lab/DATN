<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // =====================================
    // CÁC HÀM CỦA KHÁCH HÀNG (USER)
    // =====================================

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $userId = Auth::id();

        $hasBought = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $id)
            ->exists();

        if (!$hasBought) {
            return back()->with('error', 'Bạn phải mua thiết bị này trước khi đánh giá');
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

        return back()->with('success', 'Đã gửi báo cáo đánh giá!');
    }

    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $updated = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update([
                'comment' => $request->comment,
            ]);

        return response()->json([
            'success' => $updated ? true : false,
        ]);
    }

    public function deleteComment($id)
    {
        $deleted = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $deleted ? true : false,
            ]);
        }

        return back()->with('success', 'Đã xóa đánh giá của bạn');
    }
}