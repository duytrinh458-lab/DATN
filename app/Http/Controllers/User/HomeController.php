<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\News;
use Illuminate\Support\Facades\Cache; // 💡 Thêm thư viện Cache

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ phía khách hàng (Đã cải tiến tối ưu Cache)
     */
    public function index()
    {
        // 💡 Áp dụng Cache lưu trữ trong 10 phút để tăng tốc độ phản hồi trang chủ
        $data = Cache::remember('home_page_data', 600, function () {
            return [
                'productCount' => Product::count(),
                'newsCount'    => News::where('status', 'published')->count(),
                // Tự động lấy thêm 8 thiết bị UAV mới kích hoạt để hiển thị động ra trang chủ
                'featuredProducts' => Product::with('images')
                                            ->where('status', 'active')
                                            ->orderBy('id', 'desc')
                                            ->limit(8)
                                            ->get(),
                'latestNews'   => News::where('status', 'published')
                                        ->latest()
                                        ->limit(3)
                                        ->get()
            ];
        });

        return view('User.home', [
            'productCount'     => $data['productCount'],
            'newsCount'        => $data['newsCount'],
            'featuredProducts' => $data['featuredProducts'],
            'latestNews'       => $data['latestNews']
        ]);
    }
}