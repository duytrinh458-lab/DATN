<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
    // 📌 COMMENTS (thực chất là REVIEWS)
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

    // 📌 XÓA REVIEW
    public function deleteComment($id)
    {
        DB::table('reviews')->where('id', $id)->delete();

        return back()->with('success', 'Đã xóa đánh giá');
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