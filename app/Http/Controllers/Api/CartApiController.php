<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartApiController extends Controller
{
    // 📌 GET /api/cart
    public function index()
    {
        $userId = Auth::id();

        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'status' => true,
                'data' => []
            ]);
        }

        $items = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $items
        ]);
    }

    // 📌 POST /api/cart/add
    public function add(Request $request)
    {
        $userId = Auth::id();

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // lấy cart của user
        $cart = Cart::firstOrCreate([
            'user_id' => $userId
        ]);

        // check sản phẩm trong cart_items
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($item) {
            $item->quantity += $request->quantity ?? 1;
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1,
                'unit_price' => $product->price ?? 0
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã thêm vào giỏ',
            'data' => $item
        ]);
    }

    // 📌 PUT /api/cart/{product_id}
    public function update(Request $request, $product_id)
    {
        $userId = Auth::id();

        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Cart không tồn tại'
            ], 404);
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->first();

        if (!$item) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm không có trong giỏ'
            ], 404);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
            'data' => $item
        ]);
    }

    // 📌 DELETE /api/cart/{product_id}
    public function destroy($product_id)
    {
        $userId = Auth::id();

        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Cart không tồn tại'
            ], 404);
        }

        CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xoá sản phẩm'
        ]);
    }
}