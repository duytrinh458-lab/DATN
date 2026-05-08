<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsApiController extends Controller
{
    // 📌 API 52: Xem danh sách tin tức (GET /api/get_list_news)
    public function index()
    {
        // Chỉ lấy những tin ở trạng thái 'published' (đã xuất bản)
        $news = DB::table('news')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách tin tức hạm đội thành công',
            'data' => $news
        ]);
    }

    // 📌 API 53: Chi tiết bản tin (GET /api/get_news/{id})
    public function show($id)
    {
        $article = DB::table('news')
            ->where('id', $id)
            ->first();

        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy bài viết này'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $article
        ]);
    }
}