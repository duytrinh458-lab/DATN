<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\News;

class HomeController extends Controller
{
    // Trang chủ
    public function index()
    {
        $productCount = Product::count();

        $newsCount = News::where('status', 'published')->count();

        return view('User.home', compact('productCount', 'newsCount'));
    }

    // Trang sản phẩm
    public function products()
    {
        return view('User.products');
    }

    // Trang dịch vụ
    public function services()
    {
        return view('User.services');
    }

    // Trang tin tức
    public function news()
    {
        $news = News::where('status', 'published')
            ->latest()
            ->get();

        return view('User.news', compact('news'));
    }

    // Trang liên hệ
    public function contact()
    {
        return view('User.contact');
    }
}