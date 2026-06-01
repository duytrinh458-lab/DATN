<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileApiController extends Controller
{
    // 📌 API 10: Xem thông tin cá nhân
    public function me(Request $request)
    {
        return response()->json(['status' => true, 'data' => Auth::user()]);
    }

    // 📌 API 11: Cập nhật hồ sơ
    public function update(Request $request)
    {
        // TÌM ĐÚNG USER TRONG DATABASE ĐỂ TRÁNH LỖI SAVE()
        $user = User::find(Auth::id());

        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ]);

        $user->full_name = $request->full_name ?? $user->full_name;
        $user->phone     = $request->phone ?? $user->phone;

        $user->save();

        return response()->json(['status' => true, 'message' => 'Cập nhật hồ sơ thành công', 'data' => $user]);
    }

    // 📌 API change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Mật khẩu hiện tại không chính xác'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['status' => true, 'message' => 'Đổi mật khẩu thành công']);
    }
}