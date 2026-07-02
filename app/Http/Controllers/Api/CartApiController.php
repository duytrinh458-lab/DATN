<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ các API liên quan đến chức năng giỏ hàng (Shopping Cart) của người dùng.
 * Đảm bảo các luồng thêm sản phẩm, cập nhật số lượng, hiển thị và xóa sản phẩm khỏi giỏ diễn ra chính xác và an toàn.
 */

/**
 * CHỨC NĂNG CLASS:
 * Tiếp nhận các yêu cầu thay đổi giỏ hàng từ phía client (Người dùng đã đăng nhập),
 * xử lý logic ràng buộc giữa Giỏ hàng (bảng 'carts') và Chi tiết giỏ hàng (bảng 'cart_items') trước khi trả về kết quả dạng JSON.
 */
class CartApiController extends Controller
{
    // 📌 1. LẤY DANH SÁCH GIỎ HÀNG (INDEX)
    // VAI TRÒ: Hiển thị toàn bộ các sản phẩm hiện đang có trong giỏ hàng của người dùng đang đăng nhập.
    public function index()
    {
        // BIẾN QUAN TRỌNG: $userId lấy ID của người dùng hiện tại thông qua Token xác thực để cá nhân hóa giỏ hàng.
        $userId = Auth::id();
        
        // TRUY VẤN: Tìm giỏ hàng đầu tiên thuộc về người dùng này.
        $cart = Cart::where('user_id', $userId)->first();

        // KHỐI LỆNH: Nếu người dùng chưa từng tạo giỏ hàng (giỏ hàng trống hoàn toàn), trả về mảng dữ liệu rỗng thay vì báo lỗi.
        if (!$cart) {
            return response()->json(['status' => true, 'data' => []]);
        }

        // TRUY VẤN TỐI ƯU: Lấy tất cả các mặt hàng thuộc giỏ hàng này.
        // Giải thích: Sử dụng Eager Loading thông qua hàm `with('product')` để nạp kèm thông tin chi tiết của sản phẩm (tên, hình ảnh, giá...).
        // Điều này giúp hệ thống tránh được lỗi N+1 Query (tình trạng chạy quá nhiều câu lệnh SQL trong vòng lặp làm chậm hệ thống).
        $items = CartItem::with('product')->where('cart_id', $cart->id)->get();
        
        return response()->json(['status' => true, 'data' => $items]);
    }

    // 📌 2. THÊM SẢN PHẨM VÀO GIỎ HÀNG (ADD)
    // VAI TRÒ: Tiếp nhận yêu cầu thêm một mặt hàng vào giỏ. Nếu mặt hàng đã tồn tại thì tự động tăng số lượng.
    public function add(Request $request)
    {
        // 🔥 Chặn lỗi 500: Bắt buộc truyền ID sản phẩm
        // KHỐI LỆNH: Kiểm tra tính hợp lệ dữ liệu. Client bắt buộc phải truyền 'product_id' lên hệ thống.
        $request->validate(['product_id' => 'required']);

        $userId = Auth::id();
        
        // TRUY VẤN: Tìm kiếm sản phẩm trong Database xem có tồn tại hay không.
        $product = Product::find($request->product_id);

        // KHỐI LỆNH CHẶN LỖI: Nếu sản phẩm không có thật trong hệ thống (hoặc đã bị xóa), trả về lỗi 404 (Không tìm thấy sản phẩm).
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        // TRUY VẤN THÔNG MINH: Tìm giỏ hàng của user, nếu chưa có thì tự động tạo mới một giỏ hàng trống cho user đó.
        // Ý nghĩa hàm firstOrCreate: Giúp rút ngắn code, không cần phải viết câu lệnh if/else để check giỏ hàng tồn tại hay chưa.
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // TRUY VẤN: Kiểm tra xem sản phẩm này đã được cho vào giỏ hàng từ trước đó hay chưa.
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        // LUỒNG XỬ LÝ LOGIC NGHIỆP VỤ:
        if ($item) {
            // Trường hợp 1: Sản phẩm ĐÃ CÓ trong giỏ -> Tiến hành cộng dồn số lượng.
            // Sử dụng toán tử toán tắt `?? 1` để mặc định tăng thêm 1 sản phẩm nếu phía client không truyền số lượng cụ thể.
            $item->quantity += $request->quantity ?? 1;
            $item->save();
        } else {
            // Trường hợp 2: Sản phẩm CHƯA CÓ trong giỏ -> Tạo mới một bản ghi chi tiết giỏ hàng (CartItem).
            // Lấy giá bán khuyến mãi (`sale_price`) làm giá gốc của item tại thời điểm bỏ vào giỏ, nếu không có giá sale thì mặc định là 0.
            $item = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity ?? 1,
                'unit_price' => $product->sale_price ?? 0 
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Đã thêm UAV vào giỏ', 'data' => $item]);
    }

    // 📌 3. CẬP NHẬT SỐ LƯỢNG SẢN PHẨM TRONG GIỎ (UPDATE)
    // VAI TRÒ: Thay đổi trực tiếp số lượng của một sản phẩm cụ thể khi người dùng tăng/giảm số lượng ở giao diện giỏ hàng.
    public function update(Request $request, $product_id)
    {
        // 🔥 Chặn lỗi 500: Nếu không gửi quantity lên thì báo lỗi chứ không cho sập DB
        // KHỐI LỆNH: Ràng buộc dữ liệu nghiêm ngặt. Số lượng 'quantity' phải là kiểu số (numeric) và tối thiểu phải là 1 sản phẩm (min:1).
        $request->validate(['quantity' => 'required|numeric|min:1']);

        $userId = Auth::id();
        
        // TRUY VẤN: Xác định giỏ hàng của người dùng.
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) return response()->json(['status' => false, 'message' => 'Cart không tồn tại'], 404);

        // TRUY VẤN: Tìm mặt hàng cụ thể dựa trên `cart_id` và `product_id` truyền vào từ URL.
        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product_id)->first();

        // KHỐI LỆNH: Nếu sản phẩm này không nằm trong giỏ hàng hiện tại thì báo lỗi.
        if (!$item) return response()->json(['status' => false, 'message' => 'Sản phẩm không có trong giỏ'], 404);

        // XỬ LÝ: Ghi đè số lượng cũ bằng số lượng mới do client gửi lên và lưu lại.
        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['status' => true, 'message' => 'Cập nhật số lượng thành công', 'data' => $item]);
    }

    // 📌 4. XÓA MỘT SẢN PHẨM KHỎI GIỎ HÀNG (DESTROY)
    // VAI TRÒ: Loại bỏ hoàn toàn một mặt hàng cụ thể ra khỏi giỏ hàng khi người dùng bấm nút xóa (Icon thùng rác).
    public function destroy($product_id)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) return response()->json(['status' => false, 'message' => 'Cart không tồn tại'], 404);

        // TRUY VẤN XÓA: Định vị đúng sản phẩm thuộc giỏ hàng này và thực hiện lệnh `delete()` để xóa bản ghi khỏi bảng 'cart_items'.
        CartItem::where('cart_id', $cart->id)->where('product_id', $product_id)->delete();
        
        return response()->json(['status' => true, 'message' => 'Đã xoá sản phẩm khỏi giỏ']);
    }

    // 📌 5. DỌN SẠCH GIỎ HÀNG (CLEAR)
    // VAI TRÒ: Xóa toàn bộ tất cả sản phẩm đang có trong giỏ hàng (Thường dùng sau khi đặt hàng thành công hoặc người dùng muốn làm trống giỏ hàng).
    public function clear()
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();
        
        // KHỐI LỆNH: Nếu tìm thấy giỏ hàng, tiến hành xóa sạch toàn bộ các bản ghi con nằm trong bảng 'cart_items' mà có liên kết với `cart_id` này.
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }
        
        return response()->json(['status' => true, 'message' => 'Đã dọn sạch giỏ hàng']);
    }
}