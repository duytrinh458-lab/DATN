<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class UserApiController extends Controller
{
    // =========================
    // DANH SÁCH USER
    // =========================

    public function index()
    {
        $users = User::orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([

            'status'  => true,

            'message' =>
                'Lấy danh sách người dùng thành công',

            'data' =>
                UserResource::collection($users),

            'meta' => [

                'current_page' =>
                    $users->currentPage(),

                'last_page' =>
                    $users->lastPage(),

                'per_page' =>
                    $users->perPage(),

                'total' =>
                    $users->total()
            ]
        ]);
    }

    // =========================
    // CHI TIẾT USER
    // =========================

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Người dùng không tồn tại'

            ], 404);
        }

        return response()->json([

            'status' => true,

            'data'   =>
                new UserResource($user)

        ]);
    }

    // =========================
    // TẠO USER
    // =========================

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [

                'username' =>
                    'required|string|max:255|unique:users,username',

                'full_name' =>
                    'nullable|string|max:255',

                'email' =>
                    'required|email|max:255|unique:users,email',

                'phone' =>
                    'required|string|max:20|unique:users,phone',

                'password' =>
                    'required|min:6',

                'role' =>
                    'nullable|in:admin,customer'
            ]
        );

        if ($validator->fails()) {

            return response()->json([

                'status' => false,

                'errors' =>
                    $validator->errors()

            ], 422);
        }

        $user = User::create([

            'username' =>
                $request->username,

            'full_name' =>
                $request->full_name,

            'email' =>
                $request->email,

            'phone' =>
                $request->phone,

            'role' =>
                $request->role ?? 'customer',

            'password' =>
                Hash::make($request->password),

            'is_verified' => 1
        ]);

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã tạo tài khoản thành công',

            'data' =>
                new UserResource($user)

        ], 201);
    }

    // =========================
    // UPDATE USER
    // =========================

    public function update(
        Request $request,
        $id
    ) {

        $user = User::find($id);

        if (!$user) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Người dùng không tồn tại'

            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [

                'username' =>
                    'nullable|string|max:255|unique:users,username,' . $id,

                'full_name' =>
                    'nullable|string|max:255',

                'email' =>
                    'nullable|email|max:255|unique:users,email,' . $id,

                'phone' =>
                    'nullable|string|max:20|unique:users,phone,' . $id,

                'role' =>
                    'nullable|in:admin,customer',

                'password' =>
                    'nullable|min:6'
            ]
        );

        if ($validator->fails()) {

            return response()->json([

                'status' => false,

                'errors' =>
                    $validator->errors()

            ], 422);
        }

        $updateData = $request->only([

            'username',

            'full_name',

            'email',

            'phone',

            'role'
        ]);

        if ($request->filled('password')) {

            $updateData['password'] =
                Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([

            'status'  => true,

            'message' =>
                'Cập nhật thành công',

            'data' =>
                new UserResource($user)
        ]);
    }

    // =========================
    // XÓA USER
    // =========================

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Người dùng không tồn tại'

            ], 404);
        }

        if ($user->id == Auth::id()) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không thể tự xóa bản thân'

            ], 400);
        }

        $user->delete();

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã xóa người dùng'

        ]);
    }
}