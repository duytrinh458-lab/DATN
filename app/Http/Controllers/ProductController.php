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
    // ================= ADMIN =================

    // Danh sách sản phẩm
    public function index()
    {
        $products = Product::with('category', 'images')->get();
        return view('admin.products.index', compact('products'));
    }

    // Form thêm
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // Lưu sản phẩm
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Tạo sản phẩm
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'sale_price' => $request->sale_price,
                'sku' => $request->sku ?? 'UAV-' . strtoupper(Str::random(8)),
            ]);

            // Upload ảnh
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

            ProductImage::create($imageData);

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Thêm sản phẩm thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Form sửa
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // Cập nhật
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
        ]);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'sale_price' => $request->sale_price,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Cập nhật thành công!');
    }

    // Xoá
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Xoá thành công!');
    }

    // ================= USER =================

    public function products()
    {
        $products = Product::with('images')->get();
        return view('User.products.products', compact('products'));
    }
}