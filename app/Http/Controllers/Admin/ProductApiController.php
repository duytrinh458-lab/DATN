<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends Controller
{
    // 📌 GET /api/products
    public function index()
    {
        $products = Product::with('images', 'category')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    // 📌 GET /api/products/{id}
    public function show($id)
    {
        $product = Product::with('images', 'category')->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    // 📌 POST /api/products
    public function store(Request $request)
    {
        // ✅ VALIDATE
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'original_price' => 'required|numeric',
            'sale_price' => 'nullable|numeric',
            'stock' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // ✅ CHỈ LẤY FIELD CẦN THIẾT
        $product = Product::create($request->only([
            'category_id',
            'name',
            'sku',
            'description',
            'original_price',
            'sale_price',
            'stock',
            'is_featured',
            'status',
            'flight_time',
            'max_altitude',
            'camera_mp',
            'frequency',
            'weight'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Tạo sản phẩm thành công',
            'data' => $product
        ], 201);
    }

    // 📌 PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        }

        // ✅ VALIDATE
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|integer',
            'original_price' => 'sometimes|numeric',
            'sale_price' => 'nullable|numeric',
            'stock' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $product->update($request->only([
            'category_id',
            'name',
            'sku',
            'description',
            'original_price',
            'sale_price',
            'stock',
            'is_featured',
            'status',
            'flight_time',
            'max_altitude',
            'camera_mp',
            'frequency',
            'weight'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
            'data' => $product
        ]);
    }

    // 📌 DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công'
        ]);
    }
}