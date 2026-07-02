<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ hệ thống API liên quan đến Sản phẩm (Products).
 * Phục vụ cho giao diện phía Client (Xem danh sách, Tìm kiếm nâng cao, Chi tiết sản phẩm) 
 * và tích hợp quyền hạn xử lý dữ liệu cơ bản (Xóa sản phẩm chuẩn RESTful).
 */

/**
 * CHỨC NĂNG CLASS:
 * Sử dụng Eloquent ORM kết hợp Eager Loading (`with`) để tối ưu hóa hiệu năng truy vấn dữ liệu quan hệ.
 * Tích hợp thuật toán tách từ khóa để xây dựng bộ lọc tìm kiếm thông minh, đa điều kiện.
 */
class ProductApiController extends Controller
{
    // =========================================================================
    // 📌 1. TÌM KIẾM SẢN PHẨM NÂNG CAO (SEARCH)
    // =========================================================================
    // VAI TRÒ: Tìm kiếm sản phẩm theo cơ chế phân tách từ khóa (Multi-word Search), giúp tối ưu kết quả chính xác hơn dạng tìm kiếm chuỗi thô thông thường.
    public function search(Request $request)
    {
        // KHỐI LỆNH VALIDATE: Từ khóa tìm kiếm 'q' là bắt buộc, định dạng chuỗi và giới hạn tối đa 100 ký tự để tránh các cuộc tấn công DDoS qua truy vấn chuỗi dài.
        $request->validate([
            'q' => 'required|string|max:100'
        ]);

        $keyword = trim($request->q);

        // THUẬT TOÁN TÁCH TỪ: Sử dụng Biểu thức chính quy (Regex) để cắt chuỗi từ khóa thành một mảng các từ đơn dựa trên khoảng trắng.
        // Ví dụ: Từ khóa "Áo thun nam" -> ['Áo', 'thun', 'nam']
        $words = preg_split('/\s+/', $keyword);

        // TRUY VẤN TỐI ƯU: Sử dụng Eager Loading (`with`) nạp trước danh mục (category) và hình ảnh (images) để tránh lỗi N+1 Query.
        $query = Product::with(['category', 'images']);

        // LOGIC XỬ LÝ TRUY VẤN: Tạo một cụm điều kiện lồng nhau (Nested Whales).
        // Đảm bảo nguyên tắc: Mọi từ đơn sau khi tách BẮT BUỘC phải xuất hiện (Mối quan hệ AND giữa các từ), 
        // nhưng từ đơn đó có thể xuất hiện ở 1 trong 3 trường: name, sku, hoặc description (Mối quan hệ OR giữa các trường).
        $query->where(function ($q) use ($words) {

            foreach ($words as $word) {

                if (empty($word)) {
                    continue;
                }

                $q->where(function ($sub) use ($word) {

                    $sub->where('name', 'like', "%{$word}%")
                        ->orWhere('sku', 'like', "%{$word}%")
                        ->orWhere('description', 'like', "%{$word}%");

                });
            }
        });

        // SẮP XẾP & PHÂN TRANG: Đưa các sản phẩm mới nhất lên đầu và giới hạn 20 item trên một trang dữ liệu.
        $products = $query
            ->orderBy('id', 'desc')
            ->paginate(20);

        // PHẢN HỒI JSON: Cấu trúc lại dữ liệu trả về rõ ràng. 
        // Sử dụng `items()` để bóc tách riêng mảng dữ liệu sản phẩm, kết hợp với một mảng metadata riêng phục vụ việc xây dựng thanh phân trang dưới giao diện Client.
        return response()->json([
            'status' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    // =========================================================================
    // 📌 2. DANH SÁCH SẢN PHẨM MẶC ĐỊNH (INDEX)
    // =========================================================================
    // VAI TRÒ: Lấy toàn bộ danh sách sản phẩm hiện có trong hệ thống khi người dùng vừa truy cập vào trang cửa hàng.
    public function index()
    {
        // TRUY VẤN: Lấy danh sách sản phẩm kèm theo danh mục và hình ảnh liên quan, sắp xếp theo ID giảm dần.
        $products = Product::with(['category','images'])
            ->orderBy('id','desc')
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total()
            ]
        ]);
    }

    // =========================================================================
    // 📌 3. CHI TIẾT SẢN PHẨM (SHOW)
    // =========================================================================
    // VAI TRÒ: Hiển thị thông tin chi tiết đầy đủ của một sản phẩm cụ thể khi khách hàng bấm xem sản phẩm đó.
    public function show($id)
    {
        // TRUY VẤN: Tìm kiếm sản phẩm theo ID kèm theo dữ liệu liên kết.
        $product = Product::with(['category','images'])->find($id);

        // KHỐI LỆNH KIỂM TRA: Nếu không tồn tại sản phẩm trong DB, trả về mã lỗi 404 cấu trúc JSON chuẩn để phía Client xử lý điều hướng trang.
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    // =========================================================================
    // 📌 4. XÓA SẢN PHẨM (DESTROY)
    // =========================================================================
    // VAI TRÒ: Thực thi xóa bỏ hoàn toàn một bản ghi sản phẩm ra khỏi cơ sở dữ liệu theo chuẩn kiến trúc RESTful (Phương thức DELETE).
    public function destroy($id)
    {
        $product = Product::find($id);

        // CHẶN LỖI: Kiểm tra sản phẩm có tồn tại hay không trước khi gọi hàm xóa nhằm tránh kích hoạt lỗi "Call to a member function delete() on null".
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa trước đó.'
            ], 404);
        }

        // THỰC THI XÓA: Xóa bỏ dữ liệu sản phẩm.
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa sản phẩm thành công.'
        ]);
    }
}