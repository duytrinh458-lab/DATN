<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;

class CategoryController extends Controller
{
    public function categories()
    {
        $categories = Category::orderBy('id', 'desc')->get();
        return view('User.categories.index', compact('categories'));
    }

    public function byCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = Product::with(['images', 'brand'])
            ->where('category_id', $category->id)
            ->where('status', 'active');

        if ($request->filled('price_min')) {
            $query->where('sale_price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('sale_price', '<=', $request->price_max);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('flight_min')) {
            $query->where('flight_time', '>=', $request->flight_min);
        }

        if ($request->filled('camera_min')) {
            $query->where('camera_mp', '>=', $request->camera_min);
        }

        if ($request->filled('weight_max')) {
            $query->where('weight', '<=', $request->weight_max);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        $sort = $request->sort ?? 'default';

        if ($sort === 'price_asc') {
            $query->orderBy('sale_price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('sale_price', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderBy('search_count', 'desc');
        } else {
            $query->orderBy('is_featured', 'desc')
                  ->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        $brands = Brand::whereHas('products', function ($q) use ($category) {
                $q->where('category_id', $category->id)
                  ->where('status', 'active');
            })
            ->get();

        $priceRange = Product::where('category_id', $category->id)
            ->where('status', 'active')
            ->selectRaw('MIN(sale_price) as min_price, MAX(sale_price) as max_price')
            ->first();

        return view('User.categories.show', compact(
            'category', 'products', 'brands', 'priceRange'
        ));
    }
}
