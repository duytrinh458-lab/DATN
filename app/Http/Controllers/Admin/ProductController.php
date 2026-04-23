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

    public function index()
    {
        $products = Product::with(['category', 'images'])
            ->orderBy('id', 'desc')
            ->get();

        return view('Admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('Admin.products.create', compact('categories'));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'sku' => 'required|unique:products,sku',
            'image1' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $product = new Product();
            $product->category_id    = $request->category_id;
            $product->name           = $request->name;
            $product->sku            = $request->sku;
            $product->description    = $request->description;
            $product->original_price = $request->original_price ?? $request->sale_price;
            $product->sale_price     = $request->sale_price;
            $product->stock          = $request->stock ?? 0;
            $product->status         = 'active';
            $product->flight_time    = $request->flight_time;
            $product->camera_mp      = $request->camera_mp;
            $product->save();

            // 🔥 FIX TRÙNG ẢNH
            if ($request->hasFile('image1')) {
                $file = $request->file('image1');

                $fileName = 'uav_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/products'), $fileName);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => 'uploads/products/' . $fileName,
                    'position'   => 1
                ]);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Thêm UAV mới thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    // ================= EDIT =================
    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('images');

        return view('Admin.products.edit', compact('product', 'categories'));
    }

    // ================= UPDATE =================
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'sale_price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,' . $product->id,
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $product->category_id    = $request->category_id;
            $product->name           = $request->name;
            $product->sku            = $request->sku;
            $product->description    = $request->description;
            $product->original_price = $request->original_price;
            $product->sale_price     = $request->sale_price;
            $product->stock          = $request->stock;
            $product->flight_time    = $request->flight_time;
            $product->camera_mp      = $request->camera_mp;
            $product->save();

            // 🔥 CẬP NHẬT ẢNH (CHỈ KHI CÓ ẢNH MỚI)
            if ($request->hasFile('image1')) {

                // xóa file cũ
                $oldImages = ProductImage::where('product_id', $product->id)->get();
                foreach ($oldImages as $img) {
                    if (File::exists(public_path($img->image_url))) {
                        File::delete(public_path($img->image_url));
                    }
                    $img->delete();
                }

                // upload ảnh mới
                $file = $request->file('image1');

                $fileName = 'uav_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/products'), $fileName);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => 'uploads/products/' . $fileName,
                    'position'   => 1
                ]);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Cập nhật UAV thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // ================= DELETE =================
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            $images = ProductImage::where('product_id', $product->id)->get();

            foreach ($images as $img) {
                if (File::exists(public_path($img->image_url))) {
                    File::delete(public_path($img->image_url));
                }
                $img->delete();
            }

            $product->delete();

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Đã xóa UAV!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // ================= USER =================
    public function products()
    {
        $products = Product::with(['images', 'category'])
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        return view('User.products', compact('products'));
    }
}