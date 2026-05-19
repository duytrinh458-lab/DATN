<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT
    |--------------------------------------------------------------------------
    */
    public function storeComment(Request $request, $id)
{
    $request->validate([
        'comment' => 'required|string|max:1000',
        'rating'  => 'nullable|integer|min:1|max:5',
    ]);

    $userId = Auth::id();

    // LẤY ORDER HỢP LỆ
    $order = DB::table('orders')
        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
        ->where('orders.user_id', $userId)
        ->where('order_items.product_id', $id)
        ->where('orders.status', 'delivered')
        ->select('orders.id')
        ->orderByDesc('orders.id')
        ->first();

    // DEBUG BẮT BUỘC (tạm thời)
    if (!$order) {
        return back()->with('error', 'Không tìm thấy đơn hàng hợp lệ!');
    }

    // INSERT (CHẮC CHẮN CÓ order_id)
    DB::table('reviews')->insert([
        'user_id'     => $userId,
        'product_id'  => $id,
        'order_id'    => $order->id, // 🔥 QUAN TRỌNG NHẤT
        'rating'      => $request->rating ?? 5,
        'comment'     => $request->comment,
        'is_approved' => 1,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return back()->with('success', 'Đã gửi đánh giá thành công!');
}

    /*
    |--------------------------------------------------------------------------
    | UPDATE COMMENT
    |--------------------------------------------------------------------------
    */
    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $updated = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update([
                'comment'    => $request->comment,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => $updated ? true : false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE COMMENT
    |--------------------------------------------------------------------------
    */
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