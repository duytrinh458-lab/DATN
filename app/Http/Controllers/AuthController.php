<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    // 1. Gửi OTP khi đăng ký
    public function sendOtpRegister(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại phải là chữ số.',
        ]);

        $phone = $request->phone;
        $otp = rand(100000, 999999);

        DB::table('otp_verifications')->insert([
            'phone' => $phone,
            'otp_code' => $otp,
            'type' => 'register',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        // Lưu số điện thoại vào session để tự điền ở bước sau
        return redirect()->back()
            ->with('success', 'Mã OTP (giả lập) là: ' . $otp)
            ->with('phone_step1', $phone);
    }

    // 2. Xác thực OTP và tạo user
    public function verifyOtpRegister(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp_code' => 'required|digits:6',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ], [
            'email.unique' => 'Email này đã được sử dụng.',
            'otp_code.digits' => 'Mã OTP phải có 6 chữ số.',
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return redirect()->back()->withErrors(['otp_code' => 'Mã OTP không đúng hoặc đã hết hạn.'])->withInput()->with('phone_step1', $request->phone);
        }

        // tạo user mới
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verified' => 1,
            'status' => 'active'
        ]);

        // đánh dấu OTP đã dùng
        DB::table('otp_verifications')->where('id', $otp->id)->update(['is_used' => 1]);

        return redirect('/')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }

    // 3. Đăng nhập bằng email + mật khẩu
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect('/login')->with('error', 'Sai email hoặc mật khẩu');
        }

         return redirect()->route('home')->with('success', 'Đăng nhập thành công');
    }

    // 4. Gửi OTP khi quên mật khẩu
    public function sendOtpForgotPassword(Request $request)
    {
        $phone = $request->phone;
        $otp = rand(100000, 999999);

        DB::table('otp_verifications')->insert([
            'phone' => $phone,
            'otp_code' => $otp,
            'type' => 'forgot_password',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        return response()->json(['message' => 'OTP quên mật khẩu đã được tạo (test: '.$otp.')']);
    }

    // 5. Xác thực OTP và đổi mật khẩu
    public function verifyOtpForgotPassword(Request $request)
    {
        $otp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['error' => 'OTP không hợp lệ hoặc đã hết hạn'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        DB::table('otp_verifications')->where('id', $otp->id)->update(['is_used' => 1]);

        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }

    // Hàm này để bạn cứu vãn nếu Terminal bị hỏng, gõ đường dẫn /api/fix-db trên trình duyệt
    public function fixDatabase()
    {
        try {
            // Dọn dẹp cache để Laravel nhận diện các thay đổi mới nhất
            Artisan::call('optimize:clear');

            // Nếu bảng sessions đã tồn tại thì không cần chạy lại
            if (Schema::hasTable('sessions')) {
                return response()->json(['status' => 'info', 'message' => 'Bảng sessions đã tồn tại rồi!']);
            }

            // Chạy migration cưỡng ép để tạo bảng
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã tạo bảng sessions thành công! Bạn có thể quay lại trang đăng ký.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage(),
                'hint' => 'Hãy chắc chắn bạn đã xóa file database/migrations/sessions_table.php'
            ], 500);
        }
    }
}
