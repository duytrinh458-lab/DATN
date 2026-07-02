<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;

class CategoryController extends Controller
{
    // ================= 1. TRANG TỔNG HỢP TẤT CẢ DANH MỤC =================
    /**
     * Chức năng: Hiển thị toàn bộ các danh mục sản phẩm có trên hệ thống.
     */
    public function categories()
    {
        // Lấy danh sách tất cả các danh mục, ưu tiên các danh mục mới tạo lên đầu.
        $categories = Category::orderBy('id', 'desc')->get();
        
        // Trả về giao diện trang chủ danh mục (ví dụ: Danh mục UAV Nông Nghiệp, UAV Trinh Sát, Linh Kiện...)
        return view('User.categories.index', compact('categories'));
    }

    // ================= 2. TRANG CHI TIẾT DANH MỤC & BỘ LỌC TÌM KIẾM TỐI TÂN =================
    /**
     * Chức năng: Hiển thị các sản phẩm thuộc một danh mục cụ thể và xử lý bộ lọc thông minh theo yêu cầu của khách hàng.
     */
    public function byCategory(Request $request, $slug)
    {
        // 1. Định danh danh mục: Dựa vào đường dẫn (slug) trên trình duyệt để tìm đúng danh mục khách đang xem. Nếu không thấy sẽ báo trang lỗi 404.
        $category = Category::where('slug', $slug)->firstOrFail();

        // 2. Thiết lập điều kiện gốc: Chỉ tìm các sản phẩm nằm trong danh mục này VÀ phải đang ở trạng thái hiển thị (Active).
        $query = Product::with(['images', 'brand'])
            ->where('category_id', $category->id)
            ->where('status', 'active');

        // --- HỆ THỐNG BỘ LỌC THÔNG MINH (Chỉ kích hoạt khi khách hàng chọn lọc) ---

        // Lọc theo Giá tối thiểu: Khách muốn tìm sản phẩm có giá từ bao nhiêu tiền trở lên.
        if ($request->filled('price_min')) {
            $query->where('sale_price', '>=', $request->price_min);
        }

        // Lọc theo Giá tối đa: Khách muốn tìm sản phẩm có giá thấp hơn hoặc bằng bao nhiêu tiền.
        if ($request->filled('price_max')) {
            $query->where('sale_price', '<=', $request->price_max);
        }

        // Lọc theo Thương hiệu: Khách muốn mua hàng của một hãng cụ thể (ví dụ: DJI, Autel...).
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Lọc theo Thời gian bay (Tính năng đặc thù cho UAV): Khách tìm thiết bị có thời gian hoạt động trên không tối thiểu theo nhu cầu.
        if ($request->filled('flight_min')) {
            $query->where('flight_time', '>=', $request->flight_min);
        }

        // Lọc theo Độ phân giải Camera: Khách tìm thiết bị có camera từ bao nhiêu Megapixels trở lên để quay chụp sắc nét.
        if ($request->filled('camera_min')) {
            $query->where('camera_mp', '>=', $request->camera_min);
        }

        // Lọc theo Trọng lượng tối đa: Khách tìm các dòng thiết bị nhẹ, gọn gàng dưới số kg quy định.
        if ($request->filled('weight_max')) {
            $query->where('weight', '<=', $request->weight_max);
        }

        // Tùy chọn "Chỉ xem hàng còn trong kho": Tự động ẩn các sản phẩm đã hết hàng nếu khách tick vào ô này để đỡ mất thời gian.
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // --- HỆ THỐNG SẮP XẾP THỨ TỰ HIỂN THỊ ---
        $sort = $request->sort ?? 'default';

        if ($sort === 'price_asc') {
            // Sắp xếp: Giá từ thấp đến cao (Tìm hàng giá rẻ)
            $query->orderBy('sale_price', 'asc');
        } elseif ($sort === 'price_desc') {
            // Sắp xếp: Giá từ cao đến thấp (Tìm hàng cao cấp)
            $query->orderBy('sale_price', 'desc');
        } elseif ($sort === 'newest') {
            // Sắp xếp: Hàng mới về (Các sản phẩm vừa được đăng lên)
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'popular') {
            // Sắp xếp: Xem nhiều nhất (Dựa trên số lượt khách hàng tìm kiếm/bấm xem sản phẩm)
            $query->orderBy('search_count', 'desc');
        } else {
            // Sắp xếp mặc định: Đẩy các sản phẩm nổi bật (Featured) lên trước, sau đó đến hàng mới.
            $query->orderBy('is_featured', 'desc')
                  ->orderBy('created_at', 'desc');
        }

        // 3. Phân trang tự động: Mỗi trang giao diện chỉ hiển thị tối đa 12 sản phẩm để web tải nhanh hơn. 
        // Hàm withQueryString() có nhiệm vụ "giữ lại" các bộ lọc khách đã chọn khi họ bấm chuyển sang trang 2, trang 3.
        $products = $query->paginate(12)->withQueryString();

        // 4. Tiện ích sidebar thông minh: Tự động gom nhóm và chỉ hiển thị các Thương hiệu nào thực sự ĐANG CÓ SẢN PHẨM BÁN trong danh mục này (tránh việc khách bấm vào một thương hiệu nhưng bên trong không có hàng).
        $brands = Brand::whereHas('products', function ($q) use ($category) {
                $q->where('category_id', $category->id)
                  ->where('status', 'active');
            })
            ->get();

        // 5. Thước đo khoảng giá tự động: Hệ thống tự quét nhanh để tìm ra mức giá rẻ nhất (MIN) và đắt nhất (MAX) của các sản phẩm trong danh mục hiện tại, giúp lập trình viên dựng thanh kéo chọn giá (Slider) chuẩn xác cho khách dùng.
        $priceRange = Product::where('category_id', $category->id)
            ->where('status', 'active')
            ->selectRaw('MIN(sale_price) as min_price, MAX(sale_price) as max_price')
            ->first();

        // Đổ toàn bộ dữ liệu đã được xử lý tinh gọn ra giao diện trang chi tiết danh mục để khách hàng mua sắm.
        return view('User.categories.show', compact(
            'category', 'products', 'brands', 'priceRange'
        ));
    }
}