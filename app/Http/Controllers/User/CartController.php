<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    

    // 1. Xem giỏ hàng
    public function index() {
        $cartItems = Cart::with(['product.images'])
            ->where('user_id', Auth::id())
            ->get();
    
        $total = $cartItems->sum(function($item) {
            return $item->product->sale_price * $item->quantity;
        });

        return view('User.cart.index', compact('cartItems', 'total'));
    }

    // 2. Thêm vào giỏ
    public function add(Request $request) {
        $product = Product::findOrFail($request->product_id);
        
        if ($product->stock < $request->quantity) {
            return back()->with('error', 'Số lượng UAV trong kho không đủ!');
        }

        $cart = Cart::where('user_id', Auth::id())
                    ->where('product_id', $request->product_id)
                    ->first();

        if ($cart) {
            $cart->increment('quantity', $request->quantity);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }
        return redirect()->route('cart.index')->with('success', 'Đã thêm vào giỏ hàng!');
    }

    // 3. Xóa sản phẩm - Hàm mà Laravel đang báo thiếu đây
    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if ($cartItem) {
            $cartItem->delete();
            return back()->with('success', 'Đã xóa UAV khỏi giỏ hàng!');
        }

        return back()->with('error', 'Không tìm thấy sản phẩm!');
    }

    // 4. Cập nhật số lượng
    public function update(Request $request, $id)
    {
        $cartItem = Cart::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $request->quantity]);
            return back()->with('success', 'Đã cập nhật số lượng!');
        }

        return back()->with('error', 'Cập nhật thất bại!');
    }
}