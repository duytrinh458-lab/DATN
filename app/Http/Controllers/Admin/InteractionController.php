<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Review;

class InteractionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | COMMENTS
    |--------------------------------------------------------------------------
    */
    public function comments()
    {
        /*
        |--------------------------------------------------------------------------
        | ĐÁNH DẤU ĐÃ ĐỌC COMMENT MỚI
        |--------------------------------------------------------------------------
        */

        Review::where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);


        /*
        |--------------------------------------------------------------------------
        | LOAD COMMENTS
        |--------------------------------------------------------------------------
        */

        $comments = DB::table('reviews')
            ->leftJoin('users', 'users.id', '=', 'reviews.user_id')
            ->leftJoin('products', 'products.id', '=', 'reviews.product_id')
            ->select(
                'reviews.*',

                'users.full_name',
                'users.avatar',

                'products.name as product_name'
            )
            ->orderByDesc('reviews.id')
            ->paginate(20);


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.interactions.comments',
            compact('comments')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LIKES
    |--------------------------------------------------------------------------
    */
    public function likes()
    {
        return view('admin.interactions.likes');
    }


    /*
    |--------------------------------------------------------------------------
    | RATINGS
    |--------------------------------------------------------------------------
    */
    public function ratings()
    {
        return view('admin.interactions.ratings');
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
            'Đã xóa bình luận thành công'
        );
    }
}