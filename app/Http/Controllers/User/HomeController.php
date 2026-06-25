<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\News;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // BƯỚC 1: Xóa cache cũ để nó update dữ liệu mới
        \Illuminate\Support\Facades\Cache::forget('home_page_data');

        $data = Cache::remember('home_page_data', 600, function () {
            return [
                'productCount' => Product::count(),
                'newsCount'    => class_exists(News::class) ? News::count() : 0,
                'orderCount'   => \App\Models\Order::count(),
                // BƯỚC 2: Sửa điều kiện where ở đây
                // Chuyển sang lọc theo 'is_featured' = 1
                'featuredProducts' => Product::with('images')
                                            ->where('is_featured', 1) 
                                            ->orderBy('id', 'desc')
                                            ->limit(4)
                                            ->get(),

                // ĐÃ SỬA: Lọc tin tức 'published' và lấy đúng 4 bài
                'latestNews'   => class_exists(News::class) 
                                    ? News::where('status', 'published')->latest('published_at')->limit(4)->get() 
                                    : collect()
            ];
        });

        return view('User.home', [
            'productCount'     => $data['productCount'],
            'orderCount'       => $data['orderCount'],
            'newsCount'        => $data['newsCount'],
            'featuredProducts' => $data['featuredProducts'],
            'latestNews'       => $data['latestNews']
        ]);
    }
}