<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; // Thêm thư viện để xóa file ảnh

class ProductController extends Controller
{
    // ================= ADMIN =================

    // 1. Danh sách sản phẩm
    public function index()
    {
        // Sắp xếp ID mới nhất lên đầu cho Duy dễ quản lý
        $products = Product::with(['category', 'images'])->orderBy('id', 'desc')->get();
        return view('admin.products.index', compact('products'));
    }

    // 2. Form thêm mới
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // 3. Lưu sản phẩm mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'image1' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Bắt buộc ảnh chính
        ]);

        try {
            DB::beginTransaction();

            // Tạo sản phẩm (Thêm đầy đủ thông số UAV của Duy)
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'original_price' => $request->original_price ?? 0,
                'sale_price' => $request->sale_price,
                'sku' => $request->sku ?? 'UAV-' . strtoupper(Str::random(8)),
                'stock' => $request->stock ?? 0,
                'status' => $request->status ?? 'active',
                'flight_time' => $request->flight_time, // Thông số bay
                'camera_mp' => $request->camera_mp,     // Thông số camera
            ]);

            // Upload tối đa 4 ảnh
            $imageData = ['product_id' => $product->id];
            for ($i = 1; $i <= 4; $i++) {
                if ($request->hasFile("image$i")) {
                    $file = $request->file("image$i");
                    $fileName = 'uav_' . time() . "_$i." . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $fileName);
                    $imageData["image_url$i"] = 'uploads/products/' . $fileName;
                } else {
                    $imageData["image_url$i"] = null;
                }
            }
            ProductImage::create($imageData);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Thêm UAV mới thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Form sửa sản phẩm
    public function edit(Product $product)
    {
        $categories = Category::all();
        // Lấy thêm thông tin ảnh để Duy hiển thị ra form sửa
        $product->load('images');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 5. Cập nhật sản phẩm
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
        ]);

        // Cập nhật thông tin cơ bản và thông số kỹ thuật
        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'original_price' => $request->original_price,
            'sale_price' => $request->sale_price,
            'stock' => $request->stock,
            'status' => $request->status,
            'flight_time' => $request->flight_time,
            'camera_mp' => $request->camera_mp,
        ]);

        // Phần xử lý cập nhật ảnh mới Duy có thể bổ sung sau nếu cần
        return redirect()->route('products.index')->with('success', 'Cập nhật UAV thành công!');
    }

    // 6. Xoá sản phẩm (Xoá cả ảnh vật lý)
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();
            
            // Xóa file ảnh trong thư mục uploads trước
            $images = ProductImage::where('product_id', $product->id)->first();
            if ($images) {
                for ($i = 1; $i <= 4; $i++) {
                    $column = "image_url$i";
                    if ($images->$column && File::exists(public_path($images->$column))) {
                        File::delete(public_path($images->$column));
                    }
                }
                $images->delete();
            }

            $product->delete();
            
            DB::commit();
            return redirect()->route('products.index')->with('success', 'Đã xóa UAV và các tệp liên quan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Không thể xóa: ' . $e->getMessage());
        }
    }
}