<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 📋 Danh sách user
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    // 🔍 Chi tiết user
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    // ➕ FORM tạo user
    public function create()
    {
        return view('admin.users.create');
    }

    // 💾 Lưu user mới
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'nullable|string|max:255', // ✅ cho phép trống
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        User::create([
            'username' => explode('@', $request->email)[0], // ✅ FIX LỖI
            'full_name' => $request->full_name ?? null,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role, // lấy từ select box
            'status' => 'active'
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'Thêm user thành công');
    }

    // 🔄 Update role / status
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->role = $request->role;
        $user->status = $request->status;
        $user->save();

        return back()->with('success', 'Cập nhật thành công');
    }

    // ❌ Xoá user
    public function delete($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->route('admin.users')
            ->with('success', 'Đã xoá user');
    }
}