<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'images')->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'image1' => 'image|mimes:jpeg,png,jpg|max:2048', // Chỉ bắt buộc ảnh 1
        ]);

        try {
            DB::beginTransaction();

            // 2. Tạo sản phẩm
            $data = $request->all();
            $data['sku'] = $request->sku ?? 'UAV-' . strtoupper(Str::random(8));
            $product = Product::create($data);

            // 3. Xử lý upload ảnh
            $imageData = ['product_id' => $product->id];
            
            for ($i = 1; $i <= 4; $i++) {
                if ($request->hasFile("image$i")) {
                    $file = $request->file("image$i");
                    $fileName = time() . "_$i." . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $fileName);
                    $imageData["image_url$i"] = 'uploads/products/' . $fileName;
                } else {
                    $imageData["image_url$i"] = null;
                }
            }

            // 4. Lưu vào bảng product_images
            ProductImage::create($imageData);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Thêm UAV thành công!');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}