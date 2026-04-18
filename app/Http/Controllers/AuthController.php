<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ================= REGISTER =================

    public function sendOtpRegister(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11',
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

        session([
            'phone_step1' => $request->phone
        ]);

        return redirect('/register')->with('success', 'OTP của bạn là: ' . $otp);
    }

    public function verifyOtpRegister(Request $request)
    {
        $phone = session('phone_step1');

        if (!$phone) {
            return redirect('/register')->with('error', 'Thiếu số điện thoại, vui lòng gửi OTP lại');
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
            return redirect()->route('home')->with('success', 'Đăng nhập thành công');
        }

        return redirect('/login')->with('error', 'Sai email hoặc mật khẩu');
    }

    // ================= FORGOT PASSWORD =================

    public function sendOtpForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
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
            'new_password' => 'required|min:6',
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return redirect('/forgot')->with('error', 'OTP không hợp lệ hoặc hết hạn');
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