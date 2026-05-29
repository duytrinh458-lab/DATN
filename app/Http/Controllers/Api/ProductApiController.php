<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductApiController extends Controller
{
    // =========================
    // API 17: DANH SÁCH UAV
    // =========================

    public function index()
    {
        $products = Product::with([
                'category',
                'images'
            ])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([

            'status' => true,

            'data'   => $products
        ]);
    }

    // =========================
    // API 18: CHI TIẾT UAV
    // =========================

    public function show($id)
    {
        $product = Product::with([
                'category',
                'images'
            ])
            ->find($id);

        if (!$product) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy UAV'

            ], 404);
        }

        return response()->json([

            'status' => true,

            'data'   => $product
        ]);
    }

    // =========================
    // API 20: TÌM KIẾM UAV
    // =========================

    public function search(Request $request)
    {
        $request->validate([

            'q' =>
                'required|string|max:100'
        ]);

        $keyword = trim($request->q);

        $products = Product::with([
                'category',
                'images'
            ])
            ->where(
                'name',
                'like',
                "%{$keyword}%"
            )
            ->orderBy('id', 'desc')
            ->paginate(20);

        // =========================
        // SAVE SEARCH HISTORY
        // =========================

        if (Auth::check()) {

            $exists = DB::table(
                    'search_histories'
                )
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->where(
                    'keyword',
                    $keyword
                )
                ->where(
                    'created_at',
                    '>=',
                    now()->subMinutes(5)
                )
                ->exists();

            if (!$exists) {

                DB::table(
                    'search_histories'
                )->insert([

                    'user_id' =>
                        Auth::id(),

                    'keyword' =>
                        $keyword,

                    'created_at' =>
                        now()
                ]);
            }
        }

        return response()->json([

            'status' => true,

            'data'   => $products
        ]);
    }

    // =========================
    // API 21: SAVE SEARCH
    // =========================

    public function saveSearch(
        Request $request
    ) {

        $request->validate([

            'keyword' =>
                'required|string|max:100'
        ]);

        if (Auth::check()) {

            $exists = DB::table(
                    'search_histories'
                )
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->where(
                    'keyword',
                    $request->keyword
                )
                ->where(
                    'created_at',
                    '>=',
                    now()->subMinutes(5)
                )
                ->exists();

            if (!$exists) {

                DB::table(
                    'search_histories'
                )->insert([

                    'user_id' =>
                        Auth::id(),

                    'keyword' =>
                        $request->keyword,

                    'created_at' =>
                        now()
                ]);
            }
        }

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã lưu lịch sử tìm kiếm'
        ]);
    }

    // =========================
    // API 19: UAV MỚI
    // =========================

    public function checkNewItems()
    {
        $newProducts = Product::with([
                'category',
                'images'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([

            'status' => true,

            'data'   => $newProducts
        ]);
    }

    // =========================
    // API 23: COMMENTS
    // =========================

    public function getComments($id)
    {
        $comments = DB::table('reviews')
            ->join(
                'users',
                'reviews.user_id',
                '=',
                'users.id'
            )
            ->where(
                'reviews.product_id',
                $id
            )
            ->select(

                'reviews.rating',

                'reviews.comment',

                'reviews.created_at',

                'users.full_name',

                'users.avatar'
            )
            ->orderBy(
                'reviews.id',
                'desc'
            )
            ->paginate(20);

        return response()->json([

            'status' => true,

            'data'   => $comments
        ]);
    }

    // =========================
    // API 24: COMMENT PRODUCT
    // =========================

    public function setComment(
        Request $request,
        $id
    ) {

        $request->validate([

            'comment' =>
                'required|string|max:1000',

            'rating' =>
                'required|integer|min:1|max:5',

            'order_id' =>
                'required|exists:orders,id'

        ]);

        $userId = Auth::id();

        $product = Product::find($id);

        if (!$product) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy sản phẩm'

            ], 404);
        }

        // =========================
        // CHECK ORDER OWNER
        // =========================

        $orderExists = DB::table('orders')
            ->where(
                'id',
                $request->order_id
            )
            ->where(
                'user_id',
                $userId
            )
            ->exists();

        if (!$orderExists) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Bạn không có quyền đánh giá đơn hàng này'

            ], 403);
        }

        // =========================
        // CHECK DUPLICATE REVIEW
        // =========================

        $alreadyReviewed = DB::table(
                'reviews'
            )
            ->where(
                'order_id',
                $request->order_id
            )
            ->where(
                'product_id',
                $id
            )
            ->where(
                'user_id',
                $userId
            )
            ->exists();

        if ($alreadyReviewed) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Bạn đã đánh giá sản phẩm này rồi'

            ], 400);
        }

        DB::table('reviews')->insert([

            'user_id' =>
                $userId,

            'product_id' =>
                $id,

            'order_id' =>
                $request->order_id,

            'comment' =>
                $request->comment,

            'rating' =>
                $request->rating,

            'parent_id' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now()
        ]);

        return response()->json([

            'status'  => true,

            'message' =>
                'Đánh giá sản phẩm thành công!'
        ]);
    }

    // =========================
    // API 25: LIKE PRODUCT
    // =========================

    public function likeProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy sản phẩm'

            ], 404);
        }

        $userId = Auth::id();

        $liked = DB::table('product_likes')
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'product_id',
                $id
            )
            ->first();

        if ($liked) {

            DB::table('product_likes')
                ->where('id', $liked->id)
                ->delete();

            return response()->json([

                'status'  => true,

                'message' =>
                    'Đã bỏ yêu thích'
            ]);
        }

        DB::table('product_likes')->insert([

            'user_id' =>
                $userId,

            'product_id' =>
                $id,

            'created_at' =>
                now()
        ]);

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã thêm vào yêu thích'
        ]);
    }

    // ==========================================================
    // ADMIN APIs
    // ==========================================================

    // =========================
    // API 57: CREATE PRODUCT
    // =========================

    public function store(Request $request)
    {
        $request->validate([

            'name' =>
                'required|string|max:255',

            'category_id' =>
                'required|exists:categories,id',

            'original_price' =>
                'required|numeric|min:0',

            'sale_price' =>
                'required|numeric|min:0',

            'stock' =>
                'required|integer|min:0'
        ]);

        $product = Product::create([

            'name' =>
                $request->name,

            'category_id' =>
                $request->category_id,

            'brand_id' =>
                $request->brand_id,

            'sku' =>
                'UAV-' .
                strtoupper(
                    Str::random(8)
                ),

            'description' =>
                $request->description,

            'original_price' =>
                $request->original_price,

            'sale_price' =>
                $request->sale_price,

            'stock' =>
                $request->stock,

            'status' =>
                'active'
        ]);

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã thêm UAV mới',

            'data'    => $product

        ], 201);
    }

    // =========================
    // API 58: UPDATE PRODUCT
    // =========================

    public function update(
        Request $request,
        $id
    ) {

        $product = Product::find($id);

        if (!$product) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy UAV'

            ], 404);
        }

        $request->validate([

            'category_id' =>
                'nullable|integer|exists:categories,id',

            'brand_id' =>
                'nullable|integer|exists:brands,id',

            'name' =>
                'nullable|string|max:255',

            'sku' =>
                'nullable|string|max:50|unique:products,sku,' . $id,

            'original_price' =>
                'nullable|numeric|min:0',

            'sale_price' =>
                'nullable|numeric|min:0',

            'stock' =>
                'nullable|integer|min:0',

            'is_featured' =>
                'nullable|boolean',

            'status' =>
                'nullable|in:active,out_of_stock,inactive',

            'flight_time' =>
                'nullable|numeric|min:0',

            'max_altitude' =>
                'nullable|numeric|min:0',

            'camera_mp' =>
                'nullable|numeric|min:0',

            'weight' =>
                'nullable|numeric|min:0'
        ]);

        $safeData = $request->only([

            'category_id',

            'brand_id',

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
        ]);

        $product->update($safeData);

        return response()->json([

            'status'  => true,

            'message' =>
                'Cập nhật UAV thành công',

            'data'    => $product
        ]);
    }

    // =========================
    // API 59: DELETE PRODUCT
    // =========================

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy UAV'

            ], 404);
        }

        $product->delete();

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã xóa UAV'
        ]);
    }

    // =========================
    // API 15: CATEGORIES
    // =========================

    public function getCategories()
    {
        $categories = Cache::remember(
            'product_categories',
            3600,
            function () {

                return DB::table('categories')
                    ->where('is_active', 1)
                    ->get();
            }
        );

        return response()->json([

            'status' => true,

            'data'   => $categories
        ]);
    }

    // =========================
    // API 16: BRANDS
    // =========================

    public function getBrands()
    {
        $brands = Cache::remember(
            'product_brands',
            3600,
            function () {

                return DB::table('brands')
                    ->get();
            }
        );

        return response()->json([

            'status' => true,

            'data'   => $brands
        ]);
    }

    // =========================
    // API 22: SEARCH HISTORY
    // =========================

    public function getSearchHistory()
    {
        $histories = DB::table(
                'search_histories'
            )
            ->where(
                'user_id',
                Auth::id()
            )
            ->orderBy(
                'id',
                'desc'
            )
            ->limit(10)
            ->get();

        return response()->json([

            'status' => true,

            'data'   => $histories
        ]);
    }
}