<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    // 📌 API 62: Admin xem danh sách người dùng (GET /api/users)
    public function index()
    {
        // Sắp xếp người dùng mới nhất lên đầu
        $users = User::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách người dùng thành công',
            'data' => $users
        ]);
    }

    // 📌 API 63: Admin xem chi tiết 1 người dùng (GET /api/users/{id})
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy người dùng này trong hệ thống'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    // 📌 API 64: Admin tạo người dùng mới (POST /api/users)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role ?? 'customer', // Mặc định là khách hàng nếu không chọn
            'password' => Hash::make($request->password),
            'is_verified' => 1, // Admin tạo thì cho xác thực luôn
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã tạo tài khoản mới thành công',
            'data' => $user
        ], 201);
    }

    // 📌 API 64 (tiếp): Admin cập nhật thông tin người dùng (PUT /api/users/{id})
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại'], 404);
        }

        // Cập nhật các thông tin cơ bản
        $user->username = $request->username ?? $user->username;
        $user->full_name = $request->full_name ?? $user->full_name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->role = $request->role ?? $user->role;

        // Nếu Admin có nhập mật khẩu mới thì mới mã hóa và lưu
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật thông tin thành công',
            'data' => $user
        ]);
    }

    // 📌 API 65: Admin xóa người dùng (DELETE /api/users/{id})
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại'], 404);
        }

        // Không cho phép Admin tự xóa chính mình (để an toàn)
        if ($user->id == Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Bạn không thể tự xóa tài khoản của chính mình'], 400);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa người dùng khỏi hệ thống'
        ]);
    }
}