<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductApiController extends Controller
{
    // 📦 GET /api/products
    public function index()
    {
        $products = Product::with(['category', 'images'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    // 🔍 GET /api/products/{id}
    public function show($id)
    {
        $product = Product::with(['category', 'images'])->find($id);

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

    // 🔎 search
    public function search(Request $request)
    {
        $keyword = $request->q;

        $products = Product::with(['category', 'images'])
            ->where('name', 'like', "%$keyword%")
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }
}