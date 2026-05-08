<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileApiController extends Controller
{
    /**
     * 📌 API 10: Xem thông tin cá nhân (get_user_info)
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * 📌 API 11: Cập nhật hồ sơ (set_user_info)
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
        ]);

        // Cập nhật từng trường
        $user->full_name = $request->full_name ?? $user->full_name;
        $user->phone = $request->phone ?? $user->phone;
        $user->address = $request->address ?? $user->address;
        
        $user->save(); // Sẽ không còn báo đỏ nhờ dòng @var bên trên

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật hồ sơ thành công',
            'data' => $user
        ]);
    }

    /**
     * 📌 API 12: Lưu mã thiết bị nhận thông báo (set_devtoken)
     */
    public function setDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã lưu mã thiết bị thành công'
        ]);
    }

    /**
     * 📌 API 13: Lấy cài đặt thông báo (get_push_setting)
     */
    public function getPushSetting()
    {
        return response()->json([
            'status' => true,
            'allow_push' => Auth::user()->allow_push
        ]);
    }

    /**
     * 📌 API 14: Bật/Tắt thông báo (set_push_setting)
     */
    public function setPushSetting(Request $request)
    {
        $request->validate([
            'allow_push' => 'required|in:0,1'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->allow_push = $request->allow_push;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật cài đặt thông báo'
        ]);
    }

    /**
     * 📌 API: Đổi mật khẩu (change_password)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

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