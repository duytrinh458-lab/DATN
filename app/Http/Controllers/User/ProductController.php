<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        // 1. Eager Loading 'images' và 'category' để tối ưu truy vấn
        // Việc load 'category' giúp hiển thị tên danh mục trên Breadcrumb như mẫu[cite: 8]
        $product = Product::with(['images', 'category'])->findOrFail($id);

        // 2. Lấy danh sách sản phẩm liên quan (cùng danh mục, trừ sản phẩm hiện tại)[cite: 1, 15]
        // Điều này giúp tăng trải nghiệm người dùng theo chiến lược Vanguard
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        // 3. Trả về view kèm theo dữ liệu sản phẩm và sản phẩm liên quan
        return view('User.product_detail', compact('product', 'relatedProducts'));
    }
}