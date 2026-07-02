<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ hệ thống API liên quan đến Ví điện tử của người dùng (User Wallet).
 * Chu kỳ hoạt động bao gồm: Kiểm tra số dư tài khoản, truy xuất lịch sử biến động số dư, 
 * và khởi tạo yêu cầu nạp tiền an toàn qua cơ chế phê duyệt bất đồng bộ.
 */

/**
 * CHỨC NĂNG CLASS:
 * Sử dụng kết hợp Eloquent ORM (để quản lý thực thể Ví) và Query Builder (để tối ưu tốc độ ghi log giao dịch).
 * Áp dụng giải pháp thiết kế "Phòng thủ dòng tiền" bằng cách chuyển đổi cơ chế nạp tiền tự động 
 * sang trạng thái chờ duyệt (Pending State), ngăn chặn triệt để các cuộc tấn công hack tiền ảo/thao túng request.
 */
class WalletApiController extends Controller
{
    // =========================================================================
    // 🔐 TRỢ THỦ NỘI BỘ: KIỂM TRA XÁC THỰC (GET USER ID)
    // =========================================================================
    // VAI TRÒ: Hàm tiện ích nội bộ (Private Helper) dùng để cô lập logic kiểm tra Token và lấy ID người dùng đăng nhập.
    private function getUserId()
    {
        $userId = Auth::id();

        // CHẶN LỖI TRUY CẬP: Nếu không có Token hoặc Session hợp lệ, trả về ngay cấu trúc phản hồi lỗi 401 Unauthorized.
        if (!$userId) {
            return response()->json([
                'status'  => false,
                'message' => 'Chưa đăng nhập hoặc token không hợp lệ'
            ], 401);
        }

        return $userId;
    }

    // =========================================================================
    // 📌 1. LẤY SỐ DƯ VÍ HIỆN TẠI (BALANCE)
    // =========================================================================
    // VAI TRÒ: Truy xuất và hiển thị số tiền hiện có trong ví điện tử của người dùng.
    public function balance()
    {
        // KIỂM TRA QUYỀN: Gọi helper bảo mật. Nếu kết quả trả về là một JsonResponse (Lỗi 401), ngắt dòng lệnh và trả phản hồi về Client.
        $userId = $this->getUserId();
        if ($userId instanceof \Illuminate\Http\JsonResponse) return $userId;

        // KHỞI TẠO TỰ ĐỘNG (Idempotent Creation): Sử dụng `firstOrCreate` để tìm ví của người dùng.
        // Nếu đây là người dùng mới hoàn toàn chưa từng có ví, hệ thống tự động khởi tạo một bản ghi ví mới với số dư mặc định bằng 0.
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );

        return response()->json([
            'status' => true,
            'data'   => [
                'balance' => $wallet->balance
            ]
        ]);
    }

    // =========================================================================
    // 📌 2. LỊCH SỬ GIAO DỊCH (HISTORY)
    // =========================================================================
    // VAI TRÒ: Lấy danh sách toàn bộ nhật ký biến động số dư (Nạp tiền, rút tiền, thanh toán) của người dùng này.
    public function history()
    {
        $userId = $this->getUserId();
        if ($userId instanceof \Illuminate\Http\JsonResponse) return $userId;

        // TRUY VẤN THỰC THỂ: Tìm ví dựa theo ID người dùng.
        $wallet = Wallet::where('user_id', $userId)->first();

        // XỬ LÝ ĐẶC BIỆT: Nếu chưa từng có ví (đồng nghĩa chưa từng giao dịch), trả về mảng dữ liệu rỗng thay vì báo lỗi hệ thống.
        if (!$wallet) {
            return response()->json([
                'status' => true,
                'data'   => []
            ]);
        }

        // TỐI ƯU TRUY VẤN: Sử dụng `DB::table` (Query Builder) truy cập trực tiếp bảng trung gian `wallet_transactions`
        // giúp giảm tải bộ nhớ RAM so với việc nạp toàn bộ Model Eloquent, sắp xếp giao dịch mới nhất lên hàng đầu.
        $transactions = DB::table('wallet_transactions')
            ->where('wallet_id', $wallet->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $transactions
        ]);
    }

    // =========================================================================
    // 📌 3. GỬI YÊU CẦU NẠP TIỀN AN TOÀN (DEPOSIT)
    // =========================================================================
    // VAI TRÒ: Tiếp nhận yêu cầu nạp tiền từ phía Client, thực hiện lưu vết và chờ phê duyệt.
    public function deposit(Request $request)
    {
        // KHỐI LỆNH VALIDATE: Ép buộc số tiền nhập vào phải là định dạng số và tối thiểu phải từ 10,000 VND trở lên.
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ]);

        $userId = $this->getUserId();
        if ($userId instanceof \Illuminate\Http\JsonResponse) return $userId;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );

        // 💡 GIẢI PHÁP AN TOÀN HỆ THỐNG (ANTI-HACK):
        // Chỉ tạo yêu cầu nạp tiền trong cơ sở dữ liệu với trạng thái mặc định là 'pending' (Chờ duyệt).
        // Cơ chế này ngăn chặn việc Client tự ý gọi API kèm tham số tiền lớn để "bơm" tiền ảo vào hệ thống.
        DB::table('wallet_transactions')->insert([
            'wallet_id'      => $wallet->id,
            'type'           => 'deposit',
            'amount'         => $request->amount,
            'reference_code' => 'DEP-' . time(), // Mã tham chiếu duy nhất dựa trên mốc thời gian Unix
            'status'         => 'pending', 
            'created_at'     => now()
        ]);

        // 💡 LƯU Ý BẢO MẬT: Đã xóa bỏ hoàn toàn logic tự động cộng dồn trực tiếp vào `$wallet->balance` tại đây.
        // Quy trình cộng tiền thực tế sẽ được xử lý riêng biệt ở một API nội bộ khác dành riêng cho Webhook của bên Cổng thanh toán hoặc do Admin duyệt tay.

        return response()->json([
            'status'  => true,
            'message' => 'Yêu cầu nạp tiền đã được ghi nhận. Vui lòng chờ Admin phê duyệt!',
            'data'    => [
                'balance' => $wallet->balance // Số dư thực tế giữ nguyên, không thay đổi tại bước này
            ]
        ]);
    }

    // =========================================================================
    // 📌 4. XÁC NHẬN NẠP TIỀN GIẢ LẬP (CONFIRM DEPOSIT - MOCK)
    // =========================================================================
    // VAI TRÒ: Điểm cuối giả lập (Mock Endpoint) phục vụ việc phản hồi nhanh hoặc làm cổng chờ (Stub) 
    // trong quá trình phát triển giao diện phía Frontend trước khi tích hợp cổng thanh toán thật (Momo, VNPAY, Paypal).
    public function confirmDeposit(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Giao dịch đã được hệ thống xác nhận'
        ]);
    }
}