<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // =========================================
    // DANH SÁCH USER
    // =========================================
    public function index()
    {
        // paginate 5 dòng mỗi trang
        $users = User::orderBy('id', 'desc')
                    ->paginate(5);

        return view('Admin.users.index', compact('users'));
    }

    // =========================================
    // CHI TIẾT USER
    // =========================================
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('Admin.users.show', compact('user'));
    }

    // =========================================
    // FORM TẠO USER
    // =========================================
    public function create()
    {
        return view('Admin.users.create');
    }

    // =========================================
    // LƯU USER MỚI
    // =========================================
    public function store(Request $request)
    {
        $validated = $request->validate([

            'full_name' => 'nullable|string|max:100',

            'email' => 'nullable|email|max:100|unique:users,email',

            'phone' => 'required|string|max:15|unique:users,phone',

            'password' => 'required|string|min:6',

            'role' => 'required|in:admin,customer'

        ]);

        // =====================================
        // TẠO USERNAME
        // =====================================

        $username = $validated['email']
            ? explode('@', $validated['email'])[0]
            : 'user' . time();

        // tránh trùng username
        if (User::where('username', $username)->exists()) {

            $username .= rand(100, 999);

        }

        // =====================================
        // INSERT USER
        // =====================================

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

    // =========================================
    // UPDATE USER
    // =========================================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([

            'role' => 'required|in:admin,customer',

            'status' => 'required|in:active,inactive'

        ]);

        $user = User::findOrFail($id);

        $user->update([

            'role' => $validated['role'],

            'status' => $validated['status']

        ]);

        return redirect()
            ->back()
            ->with('success', 'Cập nhật người dùng thành công');
    }

    // =========================================
    // XOÁ USER
    // =========================================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // tránh tự xoá admin hiện tại
        if (auth()->id() == $user->id) {

            return redirect()
                ->back()
                ->with('error', 'Không thể xoá tài khoản hiện tại');

        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã xoá user thành công');
    }
}