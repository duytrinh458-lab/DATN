<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller trung tâm quản lý toàn bộ vòng đời sản phẩm (UAV/Drone và linh kiện liên quan).
 * Xử lý các kỹ thuật phức tạp như: Eager Loading (`with`) để tối ưu câu lệnh SQL, cơ chế Transaction bảo vệ an toàn
 * dữ liệu, kiểm soát Upload/Delete danh sách nhiều tệp tin ảnh vật lý phối hợp đồng bộ với Database.
 */
class ProductController extends Controller
{
    // ==========================================
    // 1. TRANG DANH SÁCH SẢN PHẨM (INDEX)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: index()
     * Lấy danh sách sản phẩm kèm các thông tin liên kết và hiển thị ra màn hình quản trị.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | TỐI ƯU HÓA TRUY VẤN VỚI EAGER LOADING
        |--------------------------------------------------------------------------
        | Sử dụng phương thức `with(['category', 'images', 'brand'])` giúp nạp sẵn dữ liệu liên kết.
        | Giải quyết triệt để lỗi N+1 query thường gặp trong các hệ thống lớn, giúp giảm tải tối đa cho MySQL Server.
        | Phân trang (paginate): Giới hạn hiển thị 5 sản phẩm trên mỗi trang.
        */
        $products = Product::with(['category', 'images', 'brand'])
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('Admin.products.index', compact('products'));
    }

    // ==========================================
    // 2. TRANG GIAO DIỆN THÊM MỚI (CREATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: create()
     * Lấy toàn bộ danh mục và thương hiệu hiện có để đổ vào các ô chọn (Select Option) trong Form tạo sản phẩm.
     */
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();

        return view('Admin.products.create', compact('categories', 'brands'));
    }

    // ==========================================
    // 3. XỬ LÝ LƯU SẢN PHẨM MỚI (STORE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: store()
     * Tiếp nhận thông tin cấu hình UAV, xác thực dữ liệu đầu vào chặt chẽ và lưu trữ đồng bộ (Sản phẩm + Hình ảnh).
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI XÁC THỰC DỮ LIỆU ĐẦU VÀO NGHIÊM NGẶT (VALIDATION)
        |--------------------------------------------------------------------------
        | - images: Bắt buộc phải là mảng, tối thiểu có 1 ảnh và tối đa chọn được 10 ảnh cùng lúc.
        | - images.*: Từng phần tử bên trong mảng bắt buộc phải đúng định dạng file ảnh và không nặng quá 2MB.
        */
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'sku' => 'required|max:50|unique:products,sku',
            'sale_price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,out_of_stock,inactive',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        /*
        |--------------------------------------------------------------------------
        | KHỐI ĐẢM BẢO AN TOÀN DỮ LIỆU BẰNG TRANSACTION
        |--------------------------------------------------------------------------
        | Mục đích: Đảm bảo tính "Được ăn cả, ngã về không". Nếu việc thêm sản phẩm thành công 
        | nhưng quá trình lưu ảnh gặp lỗi, toàn bộ tiến trình sẽ lập tức hủy bỏ (Rollback),
        | không để lại dữ liệu rác (Sản phẩm không có ảnh) trong Database.
        */
        DB::beginTransaction();

        try {
            // Bước 1: Khởi tạo thông tin cơ bản và các thông số kỹ thuật đặc thù của dòng UAV
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'sku' => $request->sku,
                'description' => $request->description,
                'original_price' => $request->original_price,
                'sale_price' => $request->sale_price,
                'stock' => $request->stock ?? 0,
                'is_featured' => $request->is_featured ?? 0,
                'status' => $request->status,
                'flight_time' => $request->flight_time,
                'max_altitude' => $request->max_altitude,
                'camera_mp' => $request->camera_mp,
                'frequency' => $request->frequency,
                'weight' => $request->weight,
            ]);

            // Bước 2: Duyệt mảng để xử lý tải lên danh sách nhiều hình ảnh của sản phẩm
            foreach ($request->file('images') as $index => $file) {
                // Tạo tên file độc bản kết hợp tiền tố, dấu mốc thời gian và chuỗi hash uniqid
                $fileName = 'uav_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Di chuyển file vào thư mục lưu trữ vật lý trong thư mục public
                $file->move(public_path('uploads/products'), $fileName);

                // Lưu đường dẫn ảnh vào bảng liên kết phụ 'product_images' kèm theo số thứ tự hiển thị (position)
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => 'uploads/products/' . $fileName,
                    'position' => $index + 1
                ]);
            }

            // Nếu mọi tác vụ đều trơn tru, tiến hành xác nhận lưu vĩnh viễn dữ liệu vào Database
            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Thêm sản phẩm thành công!');

        } catch (\Exception $e) {
            // Gặp sự cố khẩn cấp: Thu hồi lại toàn bộ các lệnh chèn dữ liệu trước đó để bảo vệ hệ thống
            DB::rollback();

            // Trả về trang trước, giữ lại các ô dữ liệu cũ đã nhập và hiển thị chi tiết thông báo lỗi
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ==========================================
    // 4. TRANG GIAO DIỆN CHỈNH SỬA (EDIT)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: edit()
     * Đổ toàn bộ thông tin cũ của thực thể sản phẩm cần sửa ra form chỉnh sửa.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();

        return view('Admin.products.edit', compact('product', 'categories', 'brands'));
    }

    // ==========================================
    // 5. XỬ LÝ CẬP NHẬT SẢN PHẨM (UPDATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: update()
     * Cập nhật các thông số sản phẩm. Nếu Admin tải lên loạt ảnh mới, hệ thống sẽ tiến hành xóa
     * toàn bộ tệp tin ảnh cũ trên máy chủ trước khi lưu ảnh mới để giải phóng ổ cứng.
     */
    public function update(Request $request, Product $product)
    {
        // Xác thực dữ liệu sửa đổi, loại trừ kiểm tra unique SKU của chính sản phẩm hiện tại
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|max:50|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,out_of_stock,inactive'
        ]);

        DB::beginTransaction();

        try {
            // Bước 1: Cập nhật các trường thông tin văn bản thông qua mảng lọc an toàn `only`
            $product->update($request->only([
                'name', 'sku', 'category_id', 'brand_id',
                'description', 'original_price', 'sale_price',
                'stock', 'is_featured', 'status',
                'flight_time', 'max_altitude', 'camera_mp', 'frequency', 'weight'
            ]));

            // Bước 2: Kiểm tra nếu Admin có hành động tải lên tập tin hình ảnh mới để thay thế
            if ($request->hasFile('images')) {

                // Phân mục dọn rác ổ cứng: Quét và xóa toàn bộ các tệp ảnh cũ đang nằm trong thư mục vật lý
                foreach ($product->images as $img) {
                    if (File::exists(public_path($img->image_url))) {
                        File::delete(public_path($img->image_url));
                    }
                    // Xóa dòng mô tả đường dẫn ảnh tương ứng trong bảng liên kết phụ
                    $img->delete();
                }

                // Tiến hành lưu loạt file hình ảnh mới tương tự như hàm store
                foreach ($request->file('images') as $index => $file) {
                    $fileName = 'uav_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $fileName);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => 'uploads/products/' . $fileName,
                        'position' => $index + 1
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    // ==========================================
    // 6. XỬ LÝ XÓA SẢN PHẨM (DESTROY)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: destroy()
     * Xóa sạch các tệp hình ảnh đính kèm trên máy chủ của sản phẩm trước, sau đó xóa bản ghi sản phẩm ra khỏi DB.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | DỌN SẠCH FILE MỒ CÔI TRƯỚC KHI XÓA SẢN PHẨM
            |--------------------------------------------------------------------------
            | Duyệt qua danh sách ảnh kết hợp hàm `File::delete` để giải phóng bộ nhớ ổ đĩa, 
            | đảm bảo không giữ lại các tệp tin ảnh dư thừa khi thông tin sản phẩm biến mất.
            */
            foreach ($product->images as $img) {
                if (File::exists(public_path($img->image_url))) {
                    File::delete(public_path($img->image_url));
                }
                $img->delete();
            }

            // Thực thi lệnh xóa bản ghi sản phẩm chính trong bảng 'products'
            $product->delete();

            DB::commit();

            return back()->with('success', 'Đã xóa sản phẩm');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
}