<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ hệ thống API liên quan đến Hồ sơ tài khoản người dùng (User Profile).
 * Chu kỳ hoạt động bao gồm: Truy xuất thông tin cá nhân hiện tại, cập nhật hồ sơ (Họ tên, Số điện thoại)
 * và thực hiện quy trình xác thực đổi mật khẩu bảo mật.
 */

/**
 * CHỨC NĂNG CLASS:
 * Đảm bảo an ninh thông tin cá nhân bằng cách kiểm soát chặt chẽ thông qua Auth Guard của Laravel.
 * Áp dụng cơ chế mã hóa một chiều (Hashing) để xử lý mật khẩu và Validate nghiêm ngặt dữ liệu đầu vào từ Client.
 */
class ProfileApiController extends Controller
{
    // =========================================================================
    // 📌 1. XEM THÔNG TIN CÁ NHÂN (ME)
    // =========================================================================
    // VAI TRÒ: Trả về thông tin chi tiết của tài khoản đang thực hiện request (Token hợp lệ).
    public function me(Request $request)
    {
        // Trích xuất trực tiếp thông tin User từ Facade Auth đã được xác thực qua Middleware (Sanctum/Passport).
        return response()->json([
            'status' => true, 
            'data'   => Auth::user()
        ]);
    }

    // =========================================================================
    // 📌 2. CẬP NHẬT HỒ SƠ (UPDATE)
    // =========================================================================
    // VAI TRÒ: Cho phép người dùng chỉnh sửa các thông tin cơ bản hiển thị trên hệ thống.
    public function update(Request $request)
    {
        // TÌM ĐÚNG USER TRONG DATABASE ĐỂ TRÁNH LỖI SAVE():
        // Tránh việc gọi hàm trực tiếp từ thuộc tính tĩnh, nạp thực thể User chuẩn từ DB dựa trên ID đang đăng nhập.
        $user = User::find(Auth::id());

        // KHỐI LỆNH VALIDATE: Ràng buộc định dạng. 
        // Dùng `nullable` để người dùng có thể chỉ cập nhật 1 trong 2 trường mà không bắt buộc phải gửi lên cả hai.
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ]);

        // LOGIC THAY THẾ: Nếu request gửi lên có dữ liệu mới (không null) thì cập nhật, ngược lại giữ nguyên giá trị cũ đang có trong Database.
        $user->full_name = $request->full_name ?? $user->full_name;
        $user->phone     = $request->phone ?? $user->phone;

        // Lưu thông tin thay đổi xuống cơ sở dữ liệu
        $user->save();

        return response()->json([
            'status'  => true, 
            'message' => 'Cập nhật hồ sơ thành công', 
            'data'    => $user
        ]);
    }

    // =========================================================================
    // 📌 3. ĐỔI MẬT KHẨU (CHANGE PASSWORD)
    // =========================================================================
    // VAI TRÒ: Xác thực và thay thế mật khẩu cũ bằng mật khẩu mới an toàn.
    public function changePassword(Request $request)
    {
        // KHỐI LỆNH VALIDATE: Yêu cầu bắt buộc nhập mật khẩu cũ, mật khẩu mới tối thiểu 6 ký tự.
        // Ràng buộc `confirmed` ép buộc Client phải gửi kèm trường `new_password_confirmation` có giá trị trùng khớp hoàn toàn.
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = User::find(Auth::id());

        // XÁC THỰC MẬT KHẨU HIỆN TẠI: Vì mật khẩu lưu trong DB là dạng chuỗi đã băm (Hash), 
        // bắt buộc sử dụng `Hash::check()` để đối sánh chuỗi thô gửi lên với chuỗi mã hóa trong DB.
        if (!Hash::check($request->current_password, $user->password)) {
            // Trả về mã lỗi 422 (Unprocessable Entity) khi thông tin xác thực không chính xác
            return response()->json([
                'status'  => false, 
                'message' => 'Mật khẩu hiện tại không chính xác'
            ], 422);
        }

        // MÃ HÓA AN TOÀN: Sử dụng `Hash::make()` để băm mật khẩu mới bằng thuật toán bảo mật (Bcrypt/Argon2) trước khi ghi đè vào DB.
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => true, 
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }
}