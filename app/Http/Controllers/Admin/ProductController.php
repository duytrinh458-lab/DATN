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

class ProductController extends Controller
{
    // ================= ADMIN =================

    public function index()
    {
        $products = Product::with([
                'category',
                'images',
                'brand'
            ])
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view(
            'Admin.products.index',
            compact('products')
        );
    }

    // ================= CREATE =================

    public function create()
    {
        $categories = Category::all();

        $brands = Brand::all();

        return view(
            'Admin.products.create',
            compact('categories', 'brands')
        );
    }

    // ================= STORE =================

    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|max:255',

            'category_id' => 'required|exists:categories,id',

            'brand_id' => 'nullable|exists:brands,id',

            'sku' => 'required|max:50|unique:products,sku',

            'sale_price' => 'required|numeric|min:0',

            'original_price' => 'required|numeric|min:0',

            'stock' => 'nullable|integer|min:0',

            'status' => 'required|in:active,out_of_stock,inactive',

            'flight_time' => 'nullable|numeric|min:0',

            'max_altitude' => 'nullable|numeric|min:0',

            'camera_mp' => 'nullable|numeric|min:0',

            'frequency' => 'nullable|max:50',

            'weight' => 'nullable|numeric|min:0',

            'images' => 'required|array|min:1|max:10',

            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

        ]);

        try {

            DB::beginTransaction();

            $product = new Product();

            $product->category_id = $request->category_id;

            $product->brand_id = $request->brand_id;

            $product->name = $request->name;

            $product->sku = $request->sku;

            $product->description = $request->description;

            $product->original_price = $request->original_price;

            $product->sale_price = $request->sale_price;

            $product->stock = $request->stock ?? 0;

            $product->is_featured = $request->is_featured ?? 0;

            $product->status = $request->status;

            $product->flight_time = $request->flight_time;

            $product->max_altitude = $request->max_altitude;

            $product->camera_mp = $request->camera_mp;

            $product->frequency = $request->frequency;

            $product->weight = $request->weight;

            $product->save();

            // ================= UPLOAD MULTIPLE IMAGES =================

            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $index => $file) {

                    $fileName =
                        'uav_' .
                        time() .
                        '_' .
                        uniqid() .
                        '.' .
                        $file->getClientOriginalExtension();

                    $file->move(
                        public_path('uploads/products'),
                        $fileName
                    );

                    ProductImage::create([

                        'product_id' => $product->id,

                        'image_url' =>
                            'uploads/products/' . $fileName,

                        'position' => $index + 1

                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Thêm sản phẩm UAV thành công!'
                );

        } catch (\Exception $e) {

            DB::rollback();

            return back()
                ->with(
                    'error',
                    'Lỗi: ' . $e->getMessage()
                )
                ->withInput();
        }
    }

    // ================= EDIT =================

    public function edit(Product $product)
    {
        $categories = Category::all();

        $brands = Brand::all();

        $product->load([
            'images',
            'brand',
            'category'
        ]);

        return view(
            'Admin.products.edit',
            compact(
                'product',
                'categories',
                'brands'
            )
        );
    }

    // ================= UPDATE =================

    public function update(Request $request, Product $product)
    {
        $request->validate([

            'name' => 'required|max:255',

            'category_id' => 'required|exists:categories,id',

            'brand_id' => 'nullable|exists:brands,id',

            'sku' =>
                'required|max:50|unique:products,sku,' .
                $product->id,

            'sale_price' => 'required|numeric|min:0',

            'original_price' => 'required|numeric|min:0',

            'stock' => 'nullable|integer|min:0',

            'status' => 'required|in:active,out_of_stock,inactive',

            'flight_time' => 'nullable|numeric|min:0',

            'max_altitude' => 'nullable|numeric|min:0',

            'camera_mp' => 'nullable|numeric|min:0',

            'frequency' => 'nullable|max:50',

            'weight' => 'nullable|numeric|min:0',

            'images' => 'nullable|array|max:10',

            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

        ]);

        try {

            DB::beginTransaction();

            $product->category_id = $request->category_id;

            $product->brand_id = $request->brand_id;

            $product->name = $request->name;

            $product->sku = $request->sku;

            $product->description = $request->description;

            $product->original_price = $request->original_price;

            $product->sale_price = $request->sale_price;

            $product->stock = $request->stock ?? 0;

            $product->is_featured = $request->is_featured ?? 0;

            $product->status = $request->status;

            $product->flight_time = $request->flight_time;

            $product->max_altitude = $request->max_altitude;

            $product->camera_mp = $request->camera_mp;

            $product->frequency = $request->frequency;

            $product->weight = $request->weight;

            $product->save();

            // ================= UPDATE MULTIPLE IMAGES =================

            if ($request->hasFile('images')) {

                $oldImages = ProductImage::where(
                    'product_id',
                    $product->id
                )->get();

                foreach ($oldImages as $img) {

                    if (
                        File::exists(
                            public_path($img->image_url)
                        )
                    ) {
                        File::delete(
                            public_path($img->image_url)
                        );
                    }

                    $img->delete();
                }

                foreach ($request->file('images') as $index => $file) {

                    $fileName =
                        'uav_' .
                        time() .
                        '_' .
                        uniqid() .
                        '.' .
                        $file->getClientOriginalExtension();

                    $file->move(
                        public_path('uploads/products'),
                        $fileName
                    );

                    ProductImage::create([

                        'product_id' => $product->id,

                        'image_url' =>
                            'uploads/products/' . $fileName,

                        'position' => $index + 1

                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Cập nhật sản phẩm UAV thành công!'
                );

        } catch (\Exception $e) {

            DB::rollback();

            return back()
                ->with(
                    'error',
                    'Lỗi: ' . $e->getMessage()
                )
                ->withInput();
        }
    }

    // ================= DELETE =================

    public function destroy(Product $product)
    {
        try {

            DB::beginTransaction();

            $images = ProductImage::where(
                'product_id',
                $product->id
            )->get();

            foreach ($images as $img) {

                if (
                    File::exists(
                        public_path($img->image_url)
                    )
                ) {
                    File::delete(
                        public_path($img->image_url)
                    );
                }

                $img->delete();
            }

            $product->delete();

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Đã xóa sản phẩm UAV!'
                );

        } catch (\Exception $e) {

            DB::rollback();

            return back()->with(
                'error',
                'Lỗi: ' . $e->getMessage()
            );
        }
    }
}