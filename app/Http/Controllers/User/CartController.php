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
    // ================= 1. XEM GIỎ HÀNG =================
    /**
     * Chức năng: Hiển thị danh sách các sản phẩm mà người dùng đã chọn mua.
     */
    public function index() {
        // Kiểm tra xem khách hàng đã đăng nhập chưa. Nếu chưa thì mời qua trang đăng nhập.
        $userId = Auth::id();
        if (!$userId) return redirect()->route('login');

        // Hệ thống tự động kiểm tra: Nếu khách hàng này chưa từng có giỏ hàng, hệ thống sẽ tự khởi tạo một giỏ hàng trống mới cho họ.
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // Tải danh sách sản phẩm trong giỏ, kèm theo hình ảnh minh họa của từng sản phẩm để giao diện hiển thị trực quan.
        $cartItems = CartItem::with(['product.images'])
            ->where('cart_id', $cart->id)
            ->get();

        // Tự động tính toán tổng số tiền của toàn bộ giỏ hàng (Giá ưu đãi x Số lượng từng món rồi cộng dồn lại).
        $total = $cartItems->sum(function($item) {
            return $item->product->sale_price * $item->quantity;
        });

        // Trả về giao diện trang giỏ hàng kèm theo danh sách sản phẩm và tổng tiền để khách hàng kiểm tra trước khi thanh toán.
        return view('User.cart.index', compact('cartItems', 'total'));
    }

    // ================= 2. THÊM SẢN PHẨM VÀO GIỎ =================
    /**
     * Chức năng: Xử lý khi khách bấm nút "Thêm vào giỏ hàng" tại trang chi tiết sản phẩm.
     */
    public function add(Request $request) 
    {
        // 1. Kiểm tra tính hợp lệ: Sản phẩm phải tồn tại trên hệ thống và số lượng mua phải lớn hơn hoặc bằng 1.
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        // 2. Yêu cầu đăng nhập: Chỉ thành viên đã đăng nhập mới có thể sử dụng tính năng giỏ hàng này.
        $userId = Auth::id();
        if (!$userId) return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        // 3. Kiểm tra tồn kho: Nếu số lượng khách muốn mua lớn hơn số lượng thiết bị thực tế còn trong kho, hệ thống sẽ chặn lại và báo lỗi.
        if (($product->stock ?? 0) < $quantity) {
            return back()->with('error', 'Số lượng UAV trong kho không đủ!');
        }

        // 4. Định danh giỏ hàng của khách.
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // 5. Kiểm tra trùng lặp: Tìm xem sản phẩm này đã được khách cho vào giỏ từ trước chưa.
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($cartItem) {
            // Tình huống A: Sản phẩm ĐÃ CÓ trong giỏ -> Hệ thống tự động CỘNG THÊM số lượng mới vào số lượng cũ.
            $cartItem->increment('quantity', $quantity);
        } else {
            // Tình huống B: Sản phẩm CHƯA CÓ trong giỏ -> Tạo một dòng sản phẩm mới hoàn toàn trong giỏ hàng của khách.
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $quantity,
                'unit_price' => $product->sale_price
            ]);
        }

        // Trải nghiệm người dùng (UX): Giữ khách hàng ở lại trang sản phẩm hiện tại để họ có thể tiếp tục xem và mua các sản phẩm khác, kèm thông báo thành công.
        return back()->with('success', '✔ Đã thêm UAV vào giỏ hàng thành công!');
    }

    // ================= 3. XÓA SẢN PHẨM KHỎI GIỎ =================
    /**
     * Chức năng: Loại bỏ một mặt hàng ra khỏi giỏ khi khách không còn nhu cầu mua nữa.
     */
    public function destroy($id)
    {
        $cartItem = CartItem::find($id);

        if ($cartItem) {
            // Nếu tìm thấy đúng sản phẩm đó trong giỏ -> Tiến hành xóa bỏ.
            $cartItem->delete();
            return back()->with('success', 'Đã loại bỏ UAV khỏi đội hình!');
        }

        // Trường hợp không tìm thấy dữ liệu (ví dụ: sản phẩm đã bị xóa từ trước ở một tab khác).
        return back()->with('error', 'Không tìm thấy sản phẩm!');
    }

    // ================= 4. CẬP NHẬT SỐ LƯỢNG (CHẠY NGẦM AJAX) =================
    /**
     * Chức năng: Xử lý tăng/giảm số lượng trực tiếp bằng các nút bấm (+/-) tại trang giỏ hàng.
     */
    public function update(Request $request, $id)
    {
        $cartItem = CartItem::with('product')->findOrFail($id);
        $newQuantity = $request->quantity;

        // Cơ chế chạy ngầm (AJAX): Cập nhật dữ liệu ngay lập tức mà không cần phải tải lại (F5) toàn bộ trang web, giúp trải nghiệm mượt mà.
        if ($request->wantsJson() || $request->ajax()) {
            
            // Chặn lỗi: Khách hàng không thể giảm số lượng xuống dưới 1.
            if ($newQuantity < 1) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Số lượng tối thiểu là 1 thiết bị!'
                ]);
            }
            
            // Trọng tài check kho thực tế: Khi khách tăng số lượng ở trang giỏ hàng, hệ thống lại kiểm tra kho một lần nữa để đảm bảo không vượt quá số lượng tồn kho thực tế.
            if ($newQuantity > $cartItem->product->stock) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Kho Đã Hết Hàng Số Lượng trong Kho chỉ còn ' . $cartItem->product->stock . ' chiếc!'
                ]);
            }

            // Dữ liệu hợp lệ -> Tiến hành lưu số lượng mới vào cơ sở dữ liệu.
            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            // Trả kết quả thành công về cho giao diện hiển thị số tiền mới tương ứng.
            return response()->json([
                'status' => 'success',
                'new_quantity' => $cartItem->quantity
            ]);
        }

        // Phương án dự phòng: Nếu trình duyệt không hỗ trợ chạy ngầm, hệ thống sẽ tải lại trang để cập nhật thông tin.
        return back();
    }
}