<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileApiController extends Controller
{
    // 🔐 CHECK AUTH CHUNG
    private function user()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Chưa đăng nhập hoặc token không hợp lệ'
            ], 401);
        }

        return $user;
    }

    /**
     * 📌 API 10: Xem thông tin cá nhân
     */
    public function me(Request $request)
    {
        $user = $this->user();
        if ($user instanceof \Illuminate\Http\JsonResponse) return $user;

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * 📌 API 11: Cập nhật hồ sơ
     */
    public function update(Request $request)
    {
        $user = $this->user();
        if ($user instanceof \Illuminate\Http\JsonResponse) return $user;

        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
        ]);

        $user->full_name = $request->full_name ?? $user->full_name;
        $user->phone     = $request->phone ?? $user->phone;
        $user->address   = $request->address ?? $user->address;

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật hồ sơ thành công',
            'data' => $user
        ]);
    }

    /**
     * 📌 API 12: Lưu device token
     */
    public function setDeviceToken(Request $request)
    {
        $user = $this->user();
        if ($user instanceof \Illuminate\Http\JsonResponse) return $user;

        $request->validate([
            'device_token' => 'required|string'
        ]);

        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã lưu mã thiết bị thành công'
        ]);
    }

    /**
     * 📌 API 13: Lấy setting push
     */
    public function getPushSetting()
    {
        $user = $this->user();
        if ($user instanceof \Illuminate\Http\JsonResponse) return $user;

        return response()->json([
            'status' => true,
            'allow_push' => $user->allow_push
        ]);
    }

    /**
     * 📌 API 14: set push setting
     */
    public function setPushSetting(Request $request)
    {
        $user = $this->user();
        if ($user instanceof \Illuminate\Http\JsonResponse) return $user;

        $request->validate([
            'allow_push' => 'required|in:0,1'
        ]);

        $user->allow_push = $request->allow_push;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật cài đặt thông báo'
        ]);
    }

    /**
     * 📌 API change password
     */
    public function changePassword(Request $request)
    {
        $user = $this->user();
        if ($user instanceof \Illuminate\Http\JsonResponse) return $user;

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu hiện tại không chính xác'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }
}