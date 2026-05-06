<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    // 📌 ĐĂNG KÝ (SIGNUP)
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
            'role' => 'user', // Mặc định đăng ký là khách hàng
            'password' => Hash::make($request->password),
        ]);

        // Cấp ngay 1 token khi đăng ký thành công
        $token = $user->createToken('VanguardToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Đăng ký tài khoản thành công',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    // 📌 ĐĂNG NHẬP (LOGIN)
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

        // Tạo token mới
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

    // 📌 ĐĂNG XUẤT (LOGOUT)
    public function logout(Request $request)
    {
        // Xóa token hiện tại
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã đăng xuất hệ thống'
        ]);
    }
}