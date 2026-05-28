<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource; // 🟢 Import Resource

class UserApiController extends Controller
{
    // 📌 API 62: Admin xem danh sách (ĐÃ VÁ: Phân trang + Resource)
    public function index()
    {
        // Dùng paginate(15) thay vì get() để tránh tràn RAM khi hệ thống nhiều user
        $users = User::orderBy('id', 'desc')->paginate(15);

        return response()->json([
            'status'  => true,
            'message' => 'Lấy danh sách người dùng thành công',
            'data'    => UserResource::collection($users), // 🟢 Lọc dữ liệu qua Resource
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total()
            ]
        ]);
    }

    // 📌 API 63: Admin xem chi tiết (ĐÃ VÁ: Resource)
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => new UserResource($user) // 🟢 Lọc dữ liệu qua Resource
        ]);
    }

    // 📌 API 64: Admin tạo user (ĐÃ VÁ: Chống Mass Assignment)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username'    => $request->username,
            'full_name'   => $request->full_name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'role'        => $request->role ?? 'customer',
            'password'    => Hash::make($request->password),
            'is_verified' => 1,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Đã tạo tài khoản thành công',
            'data'    => new UserResource($user) // 🟢 Lọc dữ liệu trả về
        ], 201);
    }

    // 📌 API 64 (Tiếp): Admin cập nhật (ĐÃ VÁ: Chống Mass Assignment)
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại'], 404);
        }

        // 🟢 CHỐNG MASS ASSIGNMENT: Chỉ cập nhật các trường cụ thể
        $updateData = $request->only(['username', 'full_name', 'email', 'phone', 'role']);
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật thành công',
            'data'    => new UserResource($user) // 🟢 Lọc dữ liệu trả về
        ]);
    }

    // 📌 API 65: Admin xóa người dùng
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại'], 404);
        if ($user->id == Auth::id()) return response()->json(['status' => false, 'message' => 'Không thể tự xóa bản thân'], 400);

        $user->delete();
        return response()->json(['status' => true, 'message' => 'Đã xóa người dùng']);
    }
}