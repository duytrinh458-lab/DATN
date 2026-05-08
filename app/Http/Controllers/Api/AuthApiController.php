<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Khai báo DB để tương tác với bảng otp_verifications

class AuthApiController extends Controller
{
    // 📌 1. ĐĂNG KÝ (SIGNUP)
    public function signup(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'full_name' => 'required',
            'phone' => 'required'
        ]);

        $user = User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'customer', // Dùng đúng chữ 'customer' theo CSDL
            'is_verified' => 0, // Mặc định chưa xác thực
            'password' => Hash::make($request->password),
        ]);

        // Tạo luôn mã OTP và lưu vào database khi vừa đăng ký xong
        $otp = rand(100000, 999999);
        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'register',
            'expires_at' => now()->addMinutes(15), // Hết hạn sau 15 phút
            'created_at' => now()
        ]);

        $token = $user->createToken('VanguardToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Đăng ký thành công! Mã OTP đã được gửi.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'otp_test' => $otp // Hiển thị tạm để test trên Postman
            ]
        ], 201);
    }

    // 📌 2. ĐĂNG NHẬP (LOGIN)
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác'
            ], 401);
        }

        $token = $user->createToken('VanguardToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    // 📌 3. ĐĂNG XUẤT (LOGOUT)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã đăng xuất hệ thống'
        ]);
    }

    // 📌 4. GỬI LẠI MÃ OTP (RESEND OTP)
    public function resendOtp(Request $request)
    {
        $request->validate(['phone' => 'required']);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Số điện thoại không tồn tại'], 404);
        }

        // Vô hiệu hóa các mã cũ
        DB::table('otp_verifications')->where('phone', $request->phone)->update(['is_used' => 1]);

        // Tạo mã mới
        $otp = rand(100000, 999999);
        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'register',
            'expires_at' => now()->addMinutes(15),
            'created_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã gửi lại mã OTP thành công',
            'data' => ['otp_test' => $otp] // Trả về để sinh viên dễ test Postman
        ]);
    }

    // 📌 5. XÁC THỰC OTP (VERIFY OTP)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp_code' => 'required'
        ]);

        // Kiểm tra mã OTP trong Database (còn hạn, chưa dùng, đúng loại)
        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRecord) {
            return response()->json(['status' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'], 400);
        }

        // Cập nhật trạng thái OTP đã dùng
        DB::table('otp_verifications')->where('id', $otpRecord->id)->update(['is_used' => 1]);

        // Đổi trạng thái user thành đã xác thực
        User::where('phone', $request->phone)->update(['is_verified' => 1]);

        return response()->json(['status' => true, 'message' => 'Xác thực tài khoản thành công!']);
    }

    // 📌 6. TẠO MÃ QUÊN MẬT KHẨU (CREATE CODE RESET PWD)
    public function createCodeResetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Email không tồn tại trong hệ thống'], 404);
        }

        $otp = rand(100000, 999999);
        DB::table('otp_verifications')->insert([
            'phone' => $user->phone, // Bảng OTP bắt buộc lưu phone, ta lấy phone từ user
            'otp_code' => $otp,
            'type' => 'forgot_password',
            'expires_at' => now()->addMinutes(15),
            'created_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã tạo mã khôi phục mật khẩu',
            'data' => ['otp_test' => $otp] 
        ]);
    }

    // 📌 7. KIỂM TRA MÃ KHÔI PHỤC (CHECK CODE RESET PWD)
    public function checkCodeResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $user->phone ?? '')
            ->where('otp_code', $request->code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRecord) {
            return response()->json(['status' => false, 'message' => 'Mã khôi phục không hợp lệ hoặc đã hết hạn'], 400);
        }

        return response()->json(['status' => true, 'message' => 'Mã khôi phục hợp lệ. Vui lòng nhập mật khẩu mới.']);
    }

    // 📌 8. ĐẶT LẠI MẬT KHẨU MỚI (RESET PASSWORD)
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Xóa hoặc vô hiệu hóa tất cả mã quên mật khẩu của người này cho an toàn
        DB::table('otp_verifications')->where('phone', $user->phone)->where('type', 'forgot_password')->update(['is_used' => 1]);

        return response()->json(['status' => true, 'message' => 'Đổi mật khẩu thành công! Bạn có thể đăng nhập.']);
    }

    // 📌 9. ĐỔI MẬT KHẨU KHI ĐANG ĐĂNG NHẬP (CHANGE PASSWORD)
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6'
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Mật khẩu cũ không chính xác'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['status' => true, 'message' => 'Cập nhật mật khẩu thành công']);
    }
}