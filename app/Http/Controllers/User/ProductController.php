<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // ================= DANH SÁCH =================
    public function products()
    {
        $products = Product::with('images')->latest()->get();

        return view('User.products.products', compact('products'));
    }

    // ================= CHI TIẾT =================
    public function show($id)
    {
        $product = Product::with(['images', 'category'])->findOrFail($id);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        return view('User.products.product_detail', compact('product', 'relatedProducts'));
    }

    // ================= DANH MỤC =================
    public function categories()
    {
        $categories = Category::orderBy('id', 'desc')->get();

        return view('User.categories.index', compact('categories'));
    }

    public function byCategory($id)
    {
        $category = Category::findOrFail($id);

         $products = Product::with('images')
        ->where('category_id', $id)
        ->where('status', 'active')
        ->orderBy('id', 'desc')
        ->get();

    return view('User.categories.show', compact('category', 'products'));
}
}