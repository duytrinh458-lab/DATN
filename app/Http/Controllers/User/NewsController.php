<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::where('status', 'published');

        if ($request->filled('search')) {

            $search = trim($request->search);

            // Tách từ khóa theo khoảng trắng hoặc dấu -
            $keywords = preg_split('/[\s\-]+/', strtolower($search));

            $query->where(function ($q) use ($search, $keywords) {

                // Tìm chính xác theo ID
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }

                // Mỗi từ khóa đều phải xuất hiện
                foreach ($keywords as $word) {

                    if (empty($word)) {
                        continue;
                    }

                    $q->where(function ($sub) use ($word) {

                        $sub->whereRaw('LOWER(title) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(content) LIKE ?', ["%{$word}%"]);

                    });
                }
            });
        }

        $news = $query
            ->latest()
            ->paginate(8)
            ->appends($request->query());

        return view('User.news.index', compact('news'));
    }

    public function show($id)
    {
        $news = News::where('status', 'published')
            ->findOrFail($id);

        return view('User.news.show', compact('news'));
    }
}