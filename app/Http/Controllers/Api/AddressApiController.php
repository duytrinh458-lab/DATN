<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

/**
 * MỤC ĐÍCH FILE: 
 * File này chứa các API giúp quản lý số địa chỉ nhận hàng cá nhân của người dùng (Thêm, sửa, xóa, xem) 
 * và thực hiện tính toán chi phí vận chuyển, thời gian giao hàng từ vị trí kho tổng.
 */
class AddressApiController extends Controller
{
    // 📌 API 33: Lấy danh sách địa chỉ của user (GET /api/addresses)
    // VAI TRÒ: Lấy ra toàn bộ danh sách các địa chỉ nhận hàng mà người dùng hiện tại đã từng lưu.
    public function index()
    {
        // TRUY VẤN: Tìm tất cả các dòng trong bảng địa chỉ có cột 'user_id' khớp với ID của người dùng đang đăng nhập.
        $addresses = Address::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => true,
            'data'   => $addresses
        ]);
    }

    // 📌 API 34: Thêm địa chỉ mới (POST /api/addresses)
    // VAI TRÒ: Tiếp nhận thông tin địa chỉ mới từ người dùng, kiểm tra hợp lệ, xử lý trạng thái mặc định và lưu vào database.
    public function store(Request $request)
    {
        // 🔥 ĐÃ FIX: Dùng ward thay vì city
        // KHỐI LỆNH: Ràng buộc dữ liệu đầu vào. Bắt buộc người dùng phải điền đầy đủ các thông tin này thì mới cho phép xử lý tiếp.
        $request->validate([
            'full_name' => 'required',
            'phone'     => 'required',
            'street'    => 'required',
            'district'  => 'required', 
            'city'      => 'required',
            'province'  => 'required'  
        ]);

        // BIẾN QUAN TRỌNG: $addressCount dùng để đếm xem hiện tại người dùng này đã có bao nhiêu địa chỉ trong hệ thống.
        $addressCount = Address::where('user_id', Auth::id())->count();
        
        // Ý NGHĨA: Xác định xem địa chỉ mới này có được đặt làm mặc định (is_default = 1) hay không.
        // Điều kiện: Nếu đây là địa chỉ đầu tiên được tạo ($addressCount == 0) HOẶC người dùng chủ động chọn nó làm mặc định ($request->is_default == 1).
        $isDefault = ($addressCount == 0 || $request->is_default == 1) ? 1 : 0;

        // KHỐI LỆNH: Nếu địa chỉ mới chuẩn bị lưu là địa chỉ mặc định, và trước đó họ đã có các địa chỉ khác,
        // thì ta cần phải đặt toàn bộ các địa chỉ cũ về trạng thái không mặc định (is_default = 0) để đảm bảo tài khoản chỉ có duy nhất 1 địa chỉ mặc định.
        if ($isDefault == 1 && $addressCount > 0) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        // TRUY VẤN: Tạo và lưu bản ghi địa chỉ mới vào cơ sở dữ liệu với đầy đủ các thông tin nhận từ client.
        $address = Address::create([
            'user_id'    => Auth::id(),
            'full_name'  => $request->full_name,
            'phone'      => $request->phone,
            'street'     => $request->street,
            'district'   => $request->district,
            'city'       => $request->city,
            'province'   => $request->province,
            'is_default' => $isDefault
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Thêm tọa độ nhận hàng thành công',
            'data'    => $address
        ]);
    }

    // 📌 API 35: Sửa địa chỉ (PUT /api/addresses/{id})
    // VAI TRÒ: Cập nhật thông tin mới cho một địa chỉ cụ thể đã tồn tại dựa vào ID được truyền lên thanh địa chỉ.
    public function update(Request $request, $id)
    {
        // TRUY VẤN: Tìm địa chỉ theo ID, đồng thời bắt buộc phải thuộc về người dùng hiện tại để tránh việc sửa nhầm địa chỉ của tài khoản khác.
        $address = Address::where('id', $id)->where('user_id', Auth::id())->first();

        // KHỐI LỆNH: Nếu không tìm thấy địa chỉ nào khớp với điều kiện trên, lập tức dừng lại và báo lỗi 404 (Không tìm thấy).
        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        // KHỐI LỆNH: Tương tự như khi thêm mới, nếu người dùng muốn đổi địa chỉ này thành địa chỉ mặc định chính,
        // ta phải cập nhật toàn bộ các địa chỉ khác của người dùng này về 0 để tránh xung đột.
        if ($request->is_default == 1) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        // 🔥 ĐÃ FIX: Chỉ lấy đúng dữ liệu an toàn
        // TRUY VẤN: Cập nhật các trường thông tin được phép thay đổi vào trong database.
        $address->update($request->only([
            'full_name', 'phone', 'street', 'district', 'city', 'province', 'is_default'
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật địa chỉ thành công',
            'data'    => $address
        ]);
    }

    // 📌 API 36: Xóa địa chỉ (DELETE /api/addresses/{id})
    // VAI TRÒ: Xóa bỏ hoàn toàn một địa chỉ nhận hàng ra khỏi hệ thống.
    public function destroy($id)
    {
        // TRUY VẤN: Xác minh và tìm đúng địa chỉ cần xóa của chính người dùng đang đăng nhập.
        $address = Address::where('id', $id)->where('user_id', Auth::id())->first();

        // KHỐI LỆNH: Báo lỗi nếu không tìm thấy dữ liệu phù hợp để xóa.
        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        // XỬ LÝ: Thực hiện lệnh xóa bản ghi này khỏi cơ sở dữ liệu.
        $address->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Đã xóa địa chỉ'
        ]);
    }

    // 📌 API 37: Lấy tọa độ kho hàng (GET /api/shipping/from)
    // VAI TRÒ: Cung cấp thông tin cố định về tên, địa chỉ và số điện thoại của kho hàng tổng (nơi xuất phát của hàng hóa).
    public function getShipFrom()
    {
        return response()->json([
            'status' => true,
            'data'   => [
                'name'    => 'Kho Tổng Vanguard UAV',
                'address' => 'Khu Công Nghệ Cao Hòa Lạc, Thạch Thất, Hà Nội',
                'phone'   => '19008888'
            ]
        ]);
    }

    // 📌 API 38: Xem phí Ship (GET /api/shipping/fee?address_id=1)
    // VAI TRÒ: Dựa vào địa chỉ nhận hàng của người dùng để tính toán số tiền vận chuyển và ước tính thời gian giao nhận.
    public function getShipFee(Request $request)
    {
        // BIẾN QUAN TRỌNG: $addressId lưu trữ ID địa chỉ đích mà client gửi lên qua tham số trên URL.
        $addressId = $request->query('address_id');
        
        // KHỐI LỆNH: Kiểm tra xem client có truyền ID địa chỉ lên không. Nếu thiếu thì báo lỗi ngay.
        if (!$addressId) {
            return response()->json(['status' => false, 'message' => 'Vui lòng chọn địa chỉ giao hàng'], 400);
        }

        // TRUY VẤN: Tìm thông tin chi tiết của địa chỉ này nhằm mục đích kiểm tra xem nó thuộc tỉnh/thành phố nào.
        $address = Address::where('id', $addressId)->where('user_id', Auth::id())->first();
        
        // KHỐI LỆNH: Nếu địa chỉ không tồn tại hoặc không phải của người dùng này thì báo lỗi.
        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Địa chỉ không hợp lệ'], 404);
        }

        // Ý TƯỞNG THUẬT TOÁN TÍNH PHÍ VẬN CHUYỂN:
        // 1. Mặc định gán phí ship cho tất cả các tỉnh thành xa là 50,000đ ($fee = 50000).
        // 2. Sử dụng hàm strpos() để kiểm tra xem trong tên tỉnh (province) hoặc thành phố (city) có chứa từ khóa 'hà nội' hay không (đã chuyển về chữ thường bằng strtolower để so sánh chính xác).
        // 3. Nếu có chứa từ khóa 'hà nội', tức là đơn hàng này giao nội tỉnh (gần với kho tổng Hòa Lạc) -> Ưu đãi giảm phí ship xuống còn 30,000đ.
        $fee = 50000; 
        if (strpos(strtolower($address->province), 'hà nội') !== false || strpos(strtolower($address->city), 'hà nội') !== false) {
            $fee = 30000;
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'address_id'     => $addressId,
                'destination'    => $address->province,
                'shipping_fee'   => $fee,
                // Ý NGHĨA: Sử dụng toán tử điều kiện rút gọn (if-else ngắn), nếu phí là 30k (Hà Nội) thì thời gian giao dự kiến là 1-2 ngày, ngược lại các tỉnh xa là 3-5 ngày.
                'estimated_days' => ($fee == 30000) ? '1-2 ngày' : '3-5 ngày'
            ]
        ]);
    }
}