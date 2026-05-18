<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
    // =====================================
    // CÁC HÀM CỦA QUẢN TRỊ VIÊN (ADMIN)
    // =====================================
    
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

    // 💡 Hàm này của Admin: Xóa ép buộc bình luận sai trái
    public function deleteComment($id)
    {
        $deleted = DB::table('reviews')
            ->where('id', $id)
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $deleted ? true : false,
            ]);
        }

        return back()->with('success', 'Đã xóa đánh giá (Quyền Quản Trị)');
    }
}