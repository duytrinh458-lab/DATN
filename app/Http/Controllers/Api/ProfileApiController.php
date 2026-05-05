<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileApiController extends Controller
{
    // GET /api/profile
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // PUT /api/profile
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'user' => $user
        ]);
    }

    // PUT /api/profile/password
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // kiểm tra mật khẩu cũ
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không đúng'
            ], 422);
        }

        $user->update([
            'password' => bcrypt($data['new_password'])
        ]);

        return response()->json([
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }
}