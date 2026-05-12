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
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,customer'
        ]);

        // 🔥 tạo username an toàn
        $username = $validated['email']
            ? explode('@', $validated['email'])[0]
            : 'user' . time();

        // tránh trùng username
        if (User::where('username', $username)->exists()) {
            $username .= rand(100, 999);
        }

        // 💾 insert user
        User::create([
            'username' => $username,
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active',
            'is_verified' => 1
        ]);

        return redirect()
            ->route('admin.users.index')
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

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã xoá user');
    }
}