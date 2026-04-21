<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Trang chủ
    public function index()
    {
        return view('User.home');
    }

    // Trang sản phẩm
    public function products()
{
    return view('User.products.products');
}

    // Trang dịch vụ
    public function services()
    {
        return view('services');
    }

    // Trang tin tức
    public function news()
    {
        return view('news');
    }

    // Trang liên hệ
    public function contact()
    {
        return view('contact');
    }
}
