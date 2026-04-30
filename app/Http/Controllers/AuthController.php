<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet; // Thêm Model Wallet để tạo ví
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
            'phone' => 'required|numeric|digits_between:10,11|unique:users,phone'
        ], [
            'phone.unique' => 'Số điện thoại đã tồn tại'
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
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $otp = DB::table('otp_verifications')
            ->where('phone', $phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used' , 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return redirect('/register')->with('error', 'OTP sai hoặc hết hạn');
        }

        try {
            DB::beginTransaction(); // Dùng Transaction để đảm bảo tạo cả User và Wallet

            $user = User::create([
                'username' => explode('@', $request->email)[0],
                'full_name' => $request->full_name ?? 'UAV Pilot',
                'email' => $request->email,
                'phone' => $phone,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'is_verified' => 1,
                'is_first_login' => 1,
                'status' => 'active',
            ]);

            // 🔥 ĐÚNG CHUẨN SQL: Tạo ví riêng trong bảng wallets
            DB::table('wallets')->insert([
                'user_id' => $user->id,
                'balance' => 0,
                'updated_at' => now()
            ]);

            DB::table('otp_verifications')->where('id', $otp->id)->update(['is_used' => 1]);

            DB::commit();
            session()->forget('phone_step1');

            return redirect('/login')->with('success', 'Đăng ký thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi tạo tài khoản: ' . $e->getMessage());
        }
    }

    // ================= LOGIN =================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate(); 
            $user = Auth::user();

            if ($user->is_first_login) {
                return redirect()->route('password.change.form');
            }

            return $user->role === 'admin' 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('home');
        }

        return back()->with('error', 'Sai email hoặc mật khẩu');
    }

    // ================= CHANGE PASSWORD =================
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        // 🔥 FIX LỖI ĐỎ: Tìm lại User từ Model để IDE nhận diện hàm save()
        $user = User::findOrFail(Auth::id());

        $user->password = Hash::make($request->password);
        $user->is_first_login = 0;
        $user->save();

        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')->with('success', 'Đổi mật khẩu thành công!')
            : redirect()->route('home')->with('success', 'Đổi mật khẩu thành công!');
    }

    // ================= FORGOT PASSWORD =================
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

        // 🔥 FIX LỖI ĐỎ: Đảm bảo trỏ đúng vào Model instance
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return redirect('/forgot')->with('error', 'Không tìm thấy tài khoản');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        DB::table('otp_verifications')->where('id', $otp->id)->update(['is_used' => 1]);

        return redirect('/login')->with('success', 'Đổi mật khẩu thành công');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}