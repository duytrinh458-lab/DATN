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
            'rating'  => 'required|integer|min:1|max:5',
        ]);

        $userId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | CHECK ORDER DELIVERED
        |--------------------------------------------------------------------------
        */

        $order = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $id)
            ->where('orders.status', 'delivered')
            ->select('orders.id')
            ->orderByDesc('orders.id')
            ->first();

        if (!$order) {

            return back()->with(
                'error',
                'Bạn chỉ được đánh giá sản phẩm đã nhận hàng!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK EXIST REVIEW
        |--------------------------------------------------------------------------
        */

        $existReview = DB::table('reviews')
            ->where('user_id', $userId)
            ->where('product_id', $id)
            ->first();

        if ($existReview) {

            return back()->with(
                'error',
                'Bạn đã đánh giá sản phẩm này rồi!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | INSERT REVIEW
        |--------------------------------------------------------------------------
        */

        DB::table('reviews')->insert([

            'user_id'     => $userId,

            'product_id'  => $id,

            'order_id'    => $order->id,

            'rating'      => $request->rating,

            'comment'     => $request->comment,

            'is_approved' => 1,

            'is_read'     => 0,

            'created_at'  => now(),

            'updated_at'  => now(),
        ]);

        return back()->with(
            'success',
            'Đã gửi đánh giá thành công!'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE COMMENT + RATING
    |--------------------------------------------------------------------------
    */
    public function updateComment(Request $request, $id)
    {
        $request->validate([

            'comment' => 'required|string|max:1000',

            'rating'  => 'required|integer|min:1|max:5',

        ]);

        $review = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$review) {

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đánh giá'
            ]);
        }

        $updated = DB::table('reviews')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update([

                'comment'    => $request->comment,

                'rating'     => $request->rating,

                'updated_at' => now(),

            ]);

        return response()->json([

            'success' => $updated ? true : false,

            'rating'  => $request->rating,

            'comment' => $request->comment,

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

        /*
        |--------------------------------------------------------------------------
        | AJAX RESPONSE
        |--------------------------------------------------------------------------
        */

        if (request()->expectsJson()) {

            return response()->json([
                'success' => $deleted ? true : false,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL RESPONSE
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Đã xóa đánh giá của bạn'
        );
    }
}