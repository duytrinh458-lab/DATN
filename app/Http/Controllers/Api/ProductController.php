<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        return response()->json(Product::latest()->get());
    }

    // POST /api/products
    public function store(Request $request)
    {
        $product = Product::create([
            'name' => $request->name,
            'sale_price' => $request->sale_price,
            'stock' => $request->stock,
        ]);

        return response()->json([
            'message' => 'Thêm thành công',
            'data' => $product
        ]);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        return response()->json(Product::findOrFail($id));
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'sale_price' => $request->sale_price,
            'stock' => $request->stock,
        ]);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $product
        ]);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        Product::destroy($id);

        return response()->json([
            'message' => 'Xoá thành công'
        ]);
    }
}