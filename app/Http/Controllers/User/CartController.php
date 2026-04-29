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
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('login');
        }

        $cartItems = Cart::with(['product.images'])
            ->where('user_id', $userId)
            ->get();

        $total = $cartItems->sum(function($item) {
            return $item->product->sale_price * $item->quantity;
        });

        return view('User.cart.index', compact('cartItems', 'total'));
    }

    // 2. Thêm vào giỏ
    public function add(Request $request) 
{
    // dd('ADD CART RUN');
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'nullable|integer|min:1'
    ]);

    $userId = Auth::id();

    if (!$userId) {
        return redirect()->route('login');
    }

    $product = Product::findOrFail($request->product_id);

    $quantity = $request->quantity ?? 1;

    if (($product->stock ?? 0) < $quantity) {
        return back()->with('error', 'Số lượng UAV trong kho không đủ!');
    }

    $cart = Cart::where('user_id', $userId)
                ->where('product_id', $request->product_id)
                ->first();

    if ($cart) {
        $cart->increment('quantity', $quantity);
    } else {
        Cart::create([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'quantity' => $quantity
        ]);
    }

    return redirect()->route('user.cart.index')
        ->with('success', '✔ Đã thêm vào giỏ hàng thành công!');
}

    // 3. Xóa sản phẩm
    public function destroy($id)
    {
        $userId = Auth::id();

        $cartItem = Cart::where('id', $id)
                        ->where('user_id', $userId)
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
        $userId = Auth::id();

        $cartItem = Cart::where('id', $id)
                        ->where('user_id', $userId)
                        ->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $request->quantity]);
            return back()->with('success', 'Đã cập nhật số lượng!');
        }

        return back()->with('error', 'Cập nhật thất bại!');
    }
}