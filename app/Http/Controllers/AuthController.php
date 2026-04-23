<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    // ================= REGISTER =================
    public function sendOtpRegister(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11'
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

        return redirect('/register')->with('success', 'OTP của bạn là: ' . $otp);
    }

    public function verifyOtpRegister(Request $request)
    {
        $phone = session('phone_step1');

        if (!$phone) {
            return redirect('/register')->with('error', 'Thiếu số điện thoại');
        }

        $request->validate([
            'otp_code' => 'required|digits:6',
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return redirect('/register')->with('error', 'OTP sai hoặc hết hạn');
        }

        DB::table('users')->insert([
            'username' => $request->email,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'is_verified' => 1,
            'is_first_login' => 1, // 🔥 SỬA Ở ĐÂY
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('otp_verifications')
            ->where('id', $otp->id)
            ->update(['is_used' => 1]);

        session()->forget('phone_step1');

        return redirect('/login')->with('success', 'Đăng ký thành công!');
    }

    // ================= LOGIN =================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {

            $user = Auth::user();

            // 🔥 BẮT ĐỔI MẬT KHẨU LẦN ĐẦU
            if ($user->is_first_login) {
                return redirect()->route('password.change.form');
            }

            // 🔥 PHÂN QUYỀN
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('home');
        }

        return redirect('/login')->with('error', 'Sai email hoặc mật khẩu');
    }

    // ================= CHANGE PASSWORD =================
public function showChangePasswordForm()
{
    return view('Login.change-password');
}

public function updatePassword(Request $request)
{
    $request->validate([
        'password' => 'required|min:6|confirmed'
    ]);

    $user = Auth::user();

    $user->password = Hash::make($request->password);
    $user->is_first_login = 0;
    $user->save();

    // 🔥 PHÂN QUYỀN SAU KHI ĐỔI MẬT KHẨU
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard')
            ->with('success', 'Đổi mật khẩu thành công!');
    }

    return redirect()->route('home')
        ->with('success', 'Đổi mật khẩu thành công!');
}

    // ================= FORGOT PASSWORD =================
    public function sendOtpForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric'
        ]);

        $otp = rand(100000, 999999);

        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'forgot_password',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        return redirect('/forgot')->with('success', 'OTP của bạn là: ' . $otp);
    }

    public function verifyOtpForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp_code' => 'required|digits:6',
            'new_password' => 'required|min:6'
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return redirect('/forgot')->with('error', 'OTP không hợp lệ');
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return redirect('/forgot')->with('error', 'Không tìm thấy tài khoản');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        DB::table('otp_verifications')
            ->where('id', $otp->id)
            ->update(['is_used' => 1]);

        return redirect('/login')->with('success', 'Đổi mật khẩu thành công');
    }
}