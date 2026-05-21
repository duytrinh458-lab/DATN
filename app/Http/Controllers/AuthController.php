<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // ================= VIEW =================
    public function showLogin() {
        return view('Login.login');
    }

    public function showRegister() {
        return view('Login.register');
    }

    public function showForgot() {
        return view('Login.forgot');
    }

    public function showChangePasswordForm()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        return view('Login.change-password');
    }

    // ================= REGISTER =================
    public function sendOtpRegister(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11|unique:users,phone'
        ],[
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại chỉ được chứa chữ số.',
            'phone.digits_between' => 'Số điện thoại phải từ 10 đến 11 số.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
        ]);

        $otp = rand(100000, 999999);

        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'register',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        session(['phone_step1' => $request->phone]);

        Log::info('Mã OTP Đăng ký của SĐT ' . $request->phone . ' là: ' . $otp);

        return back()->with(
            'success',
            'Mã xác thực OTP đã được gửi đến số điện thoại của bạn.'
        );
    }

    public function verifyOtpRegister(Request $request)
    {
        $phone = session('phone_step1');

        if (!$phone) {
            return redirect('/register')->with(
                'error',
                'Phiên đăng ký đã hết hạn. Vui lòng thử lại.'
            );
        }

        $request->validate([
            'otp_code' => 'required|digits:6',
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ],[
            'otp_code.required' => 'Vui lòng nhập mã OTP.',
            'otp_code.digits' => 'Mã OTP phải gồm đúng 6 số.',

            'full_name.string' => 'Họ và tên không hợp lệ.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->with(
                'error',
                'Mã OTP không chính xác hoặc đã hết hạn.'
            );
        }

        DB::beginTransaction();

        try {

            $user = User::create([
                'username' => explode('@', $request->email)[0],
                'full_name' => $request->full_name ?? 'User',
                'email' => $request->email,
                'phone' => $phone,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'is_verified' => 1,
                'is_first_login' => 1,
                'status' => 'active',
            ]);

            DB::table('wallets')->insert([
                'user_id' => $user->id,
                'balance' => 0,
                'updated_at' => now()
            ]);

            DB::table('otp_verifications')
                ->where('id', $otp->id)
                ->update(['is_used' => 1]);

            DB::commit();

            session()->forget('phone_step1');

            return redirect('/login')->with(
                'success',
                'Tài khoản của bạn đã được tạo thành công.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'
            );
        }
    }

    // ================= LOGIN =================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ],[
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {

            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->is_first_login) {
                return redirect()->route('password.change');
            }

            return $user->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return back()->with(
            'error',
            'Email hoặc mật khẩu không chính xác.'
        );
    }

    // ================= CHANGE PASSWORD =================
    public function updatePassword(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $request->validate([
            'password' => 'required|min:6|confirmed'
        ],[
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::find(Auth::id());

        $user->password = Hash::make($request->password);
        $user->is_first_login = 0;

        $user->save();

        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('home');
    }

    // ================= FORGOT PASSWORD =================
    public function sendOtpForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11'
        ],[
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại chỉ được chứa chữ số.',
            'phone.digits_between' => 'Số điện thoại phải từ 10 đến 11 số.',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->with(
                'error',
                'Không tìm thấy tài khoản với số điện thoại này.'
            );
        }

        $otp = rand(100000, 999999);

        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'forgot_password',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        session(['forgot_phone' => $request->phone]);

        Log::info('Mã OTP Quên mật khẩu của SĐT ' . $request->phone . ' là: ' . $otp);

        return back()->with(
            'success',
            'Mã xác thực OTP đã được gửi đến số điện thoại của bạn.'
        );
    }

    public function verifyOtpForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp_code' => 'required|digits:6',
            'new_password' => 'required|min:6'
        ],[
            'phone.required' => 'Thiếu số điện thoại.',

            'otp_code.required' => 'Vui lòng nhập mã OTP.',
            'otp_code.digits' => 'Mã OTP phải gồm đúng 6 số.',

            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->with(
                'error',
                'Mã OTP không chính xác hoặc đã hết hạn.'
            );
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->with(
                'error',
                'Không tìm thấy tài khoản với số điện thoại này.'
            );
        }

        $user->password = Hash::make($request->new_password);

        $user->save();

        DB::table('otp_verifications')
            ->where('id', $otp->id)
            ->update(['is_used' => 1]);

        return redirect('/login')->with(
            'success',
            'Mật khẩu đã được cập nhật thành công.'
        );
    }

    // ================= LOGOUT =================
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}