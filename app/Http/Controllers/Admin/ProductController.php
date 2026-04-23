<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    // ================= ADMIN =================

    // 1. Danh sách sản phẩm (Dành cho trang quản lý)
    public function index()
    {
        // Lấy sản phẩm kèm theo category và ảnh đầu tiên (theo quan hệ hasOne images trong Model)
        $products = Product::with(['category', 'images'])->orderBy('id', 'desc')->get();
        return view('Admin.products.index', compact('products'));
    }

    // 2. Hiển thị Form thêm mới
    public function create()
    {
        $categories = Category::all();
        return view('Admin.products.create', compact('categories'));
    }

    // 3. Xử lý lưu sản phẩm vào Database
    public function store(Request $request)
    {
        // Kiểm tra dữ liệu từ Form gửi lên
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'sku' => 'required|unique:products,sku',
            'image1' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'sku.unique' => 'Mã SKU này đã tồn tại, vui lòng nhập mã khác.',
            'image1.required' => 'Vui lòng chọn ảnh đại diện cho máy bay.',
        ]);

        try {
            DB::beginTransaction();

            // A. Lưu vào bảng products (Khớp chính xác các cột trong SQL của Duy)
            $product = Product::create([
                'category_id'    => $request->category_id,
                'name'           => $request->name,
                'sku'            => $request->sku,
                'description'    => $request->description,
                'original_price' => $request->original_price ?? $request->sale_price,
                'sale_price'     => $request->sale_price,
                'stock'          => $request->stock ?? 0,
                'status'         => 'active', // Mặc định trạng thái hoạt động
                'flight_time'    => $request->flight_time,
                'camera_mp'      => $request->camera_mp,
            ]);

            // B. Xử lý lưu ảnh vào bảng product_images (Lưu 1 dòng duy nhất cho ảnh chính)
            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $fileName = 'uav_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Đảm bảo Duy đã tạo thư mục: public/uploads/products
                $file->move(public_path('uploads/products'), $fileName);
                $filePath = 'uploads/products/' . $fileName;

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => $filePath, // Cột image_url theo đúng file SQL Duy gửi
                    'position'   => 1
                ]);
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Thêm UAV mới thành công!');

        } catch (\Exception $e) {
            DB::rollback();
            // Nếu lỗi, trả về trang cũ kèm thông báo lỗi và giữ lại dữ liệu đã nhập
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Form sửa sản phẩm
    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('images');
        return view('Admin.products.edit', compact('product', 'categories'));
    }

    // 5. Cập nhật sản phẩm
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,' . $product->id,
        ]);

        $product->update([
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'sku'            => $request->sku,
            'description'    => $request->description,
            'original_price' => $request->original_price,
            'sale_price'     => $request->sale_price,
            'stock'          => $request->stock,
            'flight_time'    => $request->flight_time,
            'camera_mp'      => $request->camera_mp,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Cập nhật UAV thành công!');
    }

    // 6. Xoá sản phẩm
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();
            
            // Xóa file ảnh trong thư mục public trước
            $images = ProductImage::where('product_id', $product->id)->get();
            foreach ($images as $img) {
                if (File::exists(public_path($img->image_url))) {
                    File::delete(public_path($img->image_url));
                }
                $img->delete();
            }

            $product->delete();
            
            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Đã xóa UAV thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Không thể xóa: ' . $e->getMessage());
        }
    }

    // ================= USER =================

    // Hiển thị danh sách sản phẩm cho khách hàng
    public function products()
    {
        $products = Product::with(['images', 'category'])
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        return view('User.products', compact('products'));
    }
}