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

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category','images','brand'])
            ->orderBy('id','desc')
            ->paginate(5);

        return view('Admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();

        return view('Admin.products.create', compact('categories','brands'));
    }

    public function store(Request $request)
    {
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

        DB::beginTransaction();

        try {

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

            // upload images
            foreach ($request->file('images') as $index => $file) {

                $fileName = 'uav_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/products'), $fileName);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => 'uploads/products/' . $fileName,
                    'position' => $index + 1
                ]);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Thêm sản phẩm thành công!');

        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|max:50|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,out_of_stock,inactive'
        ]);

        DB::beginTransaction();

        try {

            $product->update($request->only([
                'name','sku','category_id','brand_id',
                'description','original_price','sale_price',
                'stock','is_featured','status',
                'flight_time','max_altitude','camera_mp','frequency','weight'
            ]));

            // update images nếu có
            if ($request->hasFile('images')) {

                foreach ($product->images as $img) {
                    if (File::exists(public_path($img->image_url))) {
                        File::delete(public_path($img->image_url));
                    }
                    $img->delete();
                }

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
                ->with('success','Cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error',$e->getMessage());
        }
    }

    public function edit(Product $product)
{
    $categories = Category::all();
    $brands = Brand::all();

    return view('Admin.products.edit', compact('product', 'categories', 'brands'));
}

    public function destroy(Product $product)
    {
        DB::beginTransaction();

        try {

            foreach ($product->images as $img) {
                if (File::exists(public_path($img->image_url))) {
                    File::delete(public_path($img->image_url));
                }
                $img->delete();
            }

            $product->delete();

            DB::commit();

            return back()->with('success','Đã xóa sản phẩm');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error',$e->getMessage());
        }
    }
}