<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
class AuthApiController extends Controller
{
    // 📌 1. ĐĂNG KÝ (SIGNUP)
    public function signup(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'full_name' => 'required',
            'phone' => 'required|unique:users'
        ]);

        $username = explode('@', $request->email)[0] . rand(100, 999);

        $user = User::create([
            'username' => $username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'customer',
            'is_verified' => 0,
            'password' => Hash::make($request->password),
        ]);

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
            'message' => 'Đăng ký thành công! Mã OTP đã được gửi.',
            'data' => [
                'token' => $user->createToken('VanguardToken')->plainTextToken
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                // 🟢 ĐÃ FIX: Sử dụng UserResource để ẩn các cột nhạy cảm
                'user' => new UserResource($user),
                'token' => $user->createToken('VanguardToken')->plainTextToken
            ]
        ]);
    }

    // 📌 3. ĐĂNG XUẤT (LOGOUT)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'Đã đăng xuất hệ thống']);
    }

    // 📌 4. GỬI LẠI MÃ OTP (RESEND OTP)
    public function resendOtp(Request $request)
    {
        $request->validate(['phone' => 'required']);
        $user = User::where('phone', $request->phone)->first();
        if (!$user) return response()->json(['status' => false, 'message' => 'SĐT không tồn tại'], 404);

        DB::table('otp_verifications')->where('phone', $request->phone)->update(['is_used' => 1]);
        $otp = rand(100000, 999999);
        DB::table('otp_verifications')->insert([
            'phone' => $request->phone, 'otp_code' => $otp, 'type' => 'register', 'expires_at' => now()->addMinutes(15)
        ]);

        return response()->json(['status' => true, 'message' => 'Đã gửi lại OTP']);
    }

    // 📌 5. XÁC THỰC OTP (VERIFY OTP)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp_code' => 'required'
        ]);

        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRecord) return response()->json(['status' => false, 'message' => 'OTP không hợp lệ hoặc hết hạn'], 400);

        DB::table('otp_verifications')->where('id', $otpRecord->id)->update(['is_used' => 1]);
        User::where('phone', $request->phone)->update(['is_verified' => 1]);

        return response()->json(['status' => true, 'message' => 'Xác thực tài khoản thành công!']);
    }

    // 📌 6. TẠO MÃ QUÊN MẬT KHẨU
    public function createCodeResetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric'
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric'  => 'Số điện thoại chỉ được chứa chữ số.'
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) return response()->json(['status' => false, 'message' => 'Số điện thoại không tồn tại'], 404);

        $otp = rand(100000, 999999);
        DB::table('otp_verifications')->insert([
            'phone' => $request->phone, 
            'otp_code' => $otp, 
            'type' => 'forgot_password', 
            'expires_at' => now()->addMinutes(15)
        ]);

        return response()->json(['status' => true, 'message' => 'Đã tạo mã khôi phục và gửi về số điện thoại']);
    }

    // 📌 7. KIỂM TRA MÃ KHÔI PHỤC
    public function checkCodeResetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric', 
            'code'  => 'required|digits:6'
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'code.required'  => 'Vui lòng nhập mã khôi phục.',
            'code.digits'    => 'Mã khôi phục phải có đúng 6 chữ số.'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Số điện thoại không tồn tại trong hệ thống!'
            ], 404);
        }

        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRecord) return response()->json(['status' => false, 'message' => 'Mã khôi phục không hợp lệ hoặc đã hết hạn'], 400);
        
        return response()->json(['status' => true, 'message' => 'Mã khôi phục hợp lệ.']);
    }

    // 📌 8. ĐẶT LẠI MẬT KHẨU MỚI
    public function resetPassword(Request $request)
    {
        // Yêu cầu xác thực bằng số điện thoại và mã OTP
        $request->validate([
            'phone'        => 'required|numeric',
            'otp_code'     => 'required|digits:6',
            'new_password' => 'required|min:6'
        ], [
            'phone.required'        => 'Vui lòng nhập số điện thoại.',
            'phone.numeric'         => 'Số điện thoại chỉ được chứa chữ số.',
            'otp_code.required'     => 'Vui lòng nhập mã xác thực OTP.',
            'otp_code.digits'       => 'Mã OTP phải có đúng 6 chữ số.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.'
        ]);

        $user = User::where('phone', $request->phone)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Số điện thoại không tồn tại trong hệ thống!'
            ], 404);
        }

        // Đối chiếu mã OTP theo số điện thoại
        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $user->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'Mã OTP không hợp lệ, đã được sử dụng hoặc đã hết hạn!'
            ], 400);
        }

        // Cập nhật mật khẩu
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Khóa mã OTP
        DB::table('otp_verifications')
            ->where('id', $otpRecord->id)
            ->update(['is_used' => 1]);

        return response()->json([
            'status' => true, 
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }

    // 📌 9. ĐỔI MẬT KHẨU KHI ĐANG ĐĂNG NHẬP
    public function changePassword(Request $request)
    {
        $request->validate(['old_password' => 'required', 'new_password' => 'required|min:6|confirmed']);
        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Mật khẩu cũ sai'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['status' => true, 'message' => 'Cập nhật mật khẩu thành công']);
    }
}