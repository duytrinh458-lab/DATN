<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    // =========================
    // SEARCH PRODUCTS (FIXED)
    // =========================
    public function search(Request $request)
{
    $request->validate([
        'q' => 'required|string|max:100'
    ]);

    $keyword = trim($request->q);

    $words = preg_split('/\s+/', $keyword);

    $query = Product::with(['category', 'images']);

    $query->where(function ($q) use ($words) {

        foreach ($words as $word) {

            if (empty($word)) {
                continue;
            }

            $q->where(function ($sub) use ($word) {

                $sub->where('name', 'like', "%{$word}%")
                    ->orWhere('sku', 'like', "%{$word}%")
                    ->orWhere('description', 'like', "%{$word}%");

            });
        }
    });

    $products = $query
        ->orderBy('id', 'desc')
        ->paginate(20);

    return response()->json([
        'status' => true,
        'data' => $products->items(),
        'pagination' => [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]
    ]);
}

    // =========================
    // LIST PRODUCTS
    // =========================
    public function index()
    {
        $products = Product::with(['category','images'])
            ->orderBy('id','desc')
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total()
            ]
        ]);
    }

    // =========================
    // DETAIL
    // =========================
    public function show($id)
    {
        $product = Product::with(['category','images'])->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Not found'
            ],404);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }
    // ==========================================
    // THÊM MỚI: HÀM XÓA CHUẨN RESTFUL (DESTROY)
    // ==========================================
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa trước đó.'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa sản phẩm thành công.'
        ]);
    }
}