<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        // 💡 Sửa số 9 thành số 8 ở dòng paginate
        $news = \App\Models\News::where('status', 'published')
            ->latest()
            ->paginate(8);

        return view('User.news.index', compact('news'));
    }

    public function show($id)
    {
        $news = News::where('status', 'published')
            ->findOrFail($id);

        return view('User.news.show', compact('news'));
    }
}