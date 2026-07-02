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
    // ================= TRANG CHỦ WEBSITE =================
    /**
     * Chức năng: Thu thập và hiển thị toàn bộ dữ liệu trên trang chủ website UAV.
     * Bao gồm: Số lượng hệ thống, danh sách UAV nổi bật và tin tức công nghệ mới nhất.
     */
    public function index()
    {
        // BƯỚC 1: Lệnh xóa bộ nhớ đệm (Cache) cũ để hệ thống bắt buộc phải cập nhật lại dữ liệu mới nhất từ Database.
        \Illuminate\Support\Facades\Cache::forget('home_page_data');

        // Hệ thống kiểm tra Bộ nhớ đệm (Cache): Nếu có dữ liệu rồi thì lấy ra dùng luôn cho web tải nhanh siêu tốc (trong 600 giây).
        // Nếu chưa có hoặc vừa bị xóa ở BƯỚC 1, hệ thống sẽ chạy hàm bên dưới để gom dữ liệu mới.
        $data = Cache::remember('home_page_data', 600, function () {
            return [
                // Đếm tổng số lượng thiết bị UAV đang có trong kho để hiển thị số liệu thống kê.
                'productCount' => Product::count(),
                
                // Kiểm tra an toàn: Nếu bảng Tin tức (News) tồn tại thì đếm tổng số bài viết, nếu không thì trả về 0 để tránh lỗi sập web.
                'newsCount'    => class_exists(News::class) ? News::count() : 0,
<<<<<<< HEAD
                'orderCount'   => \App\Models\Order::count(),
                // BƯỚC 2: Sửa điều kiện where ở đây
                // Chuyển sang lọc theo 'is_featured' = 1
=======
                
                // BƯỚC 2: Hệ thống lọc danh sách "Sản phẩm nổi bật" (is_featured = 1).
                // Lấy kèm hình ảnh, sắp xếp theo thứ tự mới nhất (ID giảm dần) và giới hạn đúng 4 thiết bị đẳng cấp nhất để trưng bày ở trang chủ.
>>>>>>> 238a99f (comment)
                'featuredProducts' => Product::with('images')
                                            ->where('is_featured', 1) 
                                            ->orderBy('id', 'desc')
                                            ->limit(4)
                                            ->get(),

                // Hệ thống lọc tin tức: Chỉ lấy các bài viết đã được duyệt đăng (published),
                // sắp xếp theo thời gian xuất bản mới nhất và lấy đúng 4 bài hot nhất. Nếu không có bảng tin tức sẽ trả về mảng rỗng.
                'latestNews'   => class_exists(News::class) 
                                    ? News::where('status', 'published')->latest('published_at')->limit(4)->get() 
                                    : collect()
            ];
        });

        // Đổ toàn bộ dữ liệu (Số lượng, UAV nổi bật, Tin tức) ra File giao diện (Blade View) của trang chủ để hiển thị cho khách hàng.
        return view('User.home', [
            'productCount'     => $data['productCount'],
            'orderCount'       => $data['orderCount'],
            'newsCount'        => $data['newsCount'],
            'featuredProducts' => $data['featuredProducts'],
            'latestNews'       => $data['latestNews']
        ]);
    }
}