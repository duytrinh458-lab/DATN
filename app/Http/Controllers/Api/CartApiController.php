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
    public function index()
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $items = CartItem::with('product')->where('cart_id', $cart->id)->get();
        return response()->json(['status' => true, 'data' => $items]);
    }

    public function add(Request $request)
    {
        // 🔥 Chặn lỗi 500: Bắt buộc truyền ID sản phẩm
        $request->validate(['product_id' => 'required']);

        $userId = Auth::id();
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $cart = Cart::firstOrCreate(['user_id' => $userId]);

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
                'unit_price' => $product->sale_price ?? 0 
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Đã thêm UAV vào giỏ', 'data' => $item]);
    }

    public function update(Request $request, $product_id)
    {
        // 🔥 Chặn lỗi 500: Nếu không gửi quantity lên thì báo lỗi chứ không cho sập DB
        $request->validate(['quantity' => 'required|numeric|min:1']);

        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) return response()->json(['status' => false, 'message' => 'Cart không tồn tại'], 404);

        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product_id)->first();

        if (!$item) return response()->json(['status' => false, 'message' => 'Sản phẩm không có trong giỏ'], 404);

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['status' => true, 'message' => 'Cập nhật số lượng thành công', 'data' => $item]);
    }

    public function destroy($product_id)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) return response()->json(['status' => false, 'message' => 'Cart không tồn tại'], 404);

        CartItem::where('cart_id', $cart->id)->where('product_id', $product_id)->delete();
        return response()->json(['status' => true, 'message' => 'Đã xoá sản phẩm khỏi giỏ']);
    }

    public function clear()
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }
        return response()->json(['status' => true, 'message' => 'Đã dọn sạch giỏ hàng']);
    }
}