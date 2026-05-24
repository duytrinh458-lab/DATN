<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // 1. Xem giỏ hàng
    public function index() {
        $userId = Auth::id();
        if (!$userId) return redirect()->route('login');

        // Tìm giỏ hàng, nếu chưa có thì tạo mới
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // Lấy chi tiết các sản phẩm trong giỏ kèm theo ảnh
        $cartItems = CartItem::with(['product.images'])
            ->where('cart_id', $cart->id)
            ->get();

        $total = $cartItems->sum(function($item) {
            return $item->product->sale_price * $item->quantity;
        });

        return view('User.cart.index', compact('cartItems', 'total'));
    }

    // 2. Thêm vào giỏ
    public function add(Request $request) 
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $userId = Auth::id();
        if (!$userId) return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        if (($product->stock ?? 0) < $quantity) {
            return back()->with('error', 'Số lượng UAV trong kho không đủ!');
        }

        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // Tìm xem sản phẩm này đã có trong giỏ hàng cụ thể này chưa
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $quantity,
                'unit_price' => $product->sale_price
            ]);
        }

        // Thay vì chuyển thẳng vào giỏ, ta cho khách ở lại trang sản phẩm để mua tiếp
        return back()->with('success', '✔ Đã thêm UAV vào giỏ hàng thành công!');
    }

    // 3. Xóa sản phẩm
    public function destroy($id)
    {
        $cartItem = CartItem::find($id);

        if ($cartItem) {
            $cartItem->delete();
            return back()->with('success', 'Đã loại bỏ UAV khỏi đội hình!');
        }

        return back()->with('error', 'Không tìm thấy sản phẩm!');
    }

    // 4. Cập nhật số lượng (Controller làm trọng tài)
    public function update(Request $request, $id)
    {
        $cartItem = CartItem::with('product')->findOrFail($id);
        $newQuantity = $request->quantity;

        // Xử lý khi Frontend gọi ngầm (AJAX)
        if ($request->wantsJson() || $request->ajax()) {
            
            if ($newQuantity < 1) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Số lượng tối thiểu là 1 thiết bị!'
                ]);
            }
            
            // CONTROLLER TRỰC TIẾP CHECK TỒN KHO VÀ BÁO LỖI
            if ($newQuantity > $cartItem->product->stock) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Kho Đã Hết Hàng Số Lượng trong Kho chỉ còn ' . $cartItem->product->stock . ' chiếc!'
                ]);
            }

            // Nếu an toàn thì lưu
            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            return response()->json([
                'status' => 'success',
                'new_quantity' => $cartItem->quantity
            ]);
        }

        // Dự phòng (non-ajax)
        return back();
    }
}