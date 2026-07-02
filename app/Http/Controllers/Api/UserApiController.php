<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý hệ thống API CRUD (Create, Read, Update, Delete) người dùng (User Management).
 * Thường được sử dụng trong các trang quản trị (Admin Dashboard) để kiểm soát, phân quyền 
 * và quản lý danh sách tài khoản toàn hệ thống.
 */

/**
 * CHỨC NĂNG CLASS:
 * Tích hợp Eloquent ORM để truy xuất dữ liệu, Validator độc lập để kiểm soát lỗi chặt chẽ,
 * và sử dụng API Resource (UserResource) nhằm chuẩn hóa cấu trúc JSON trả về, bảo mật các thông tin nhạy cảm.
 */
class UserApiController extends Controller
{
    // =========================================================================
    // 📌 1. LẤY DANH SÁCH NGƯỜI DÙNG (INDEX)
    // =========================================================================
    // VAI TRÒ: Truy xuất danh sách toàn bộ người dùng có phân trang, sắp xếp theo tài khoản mới nhất.
    public function index()
    {
        // TRUY VẤN: Lấy danh sách sắp xếp giảm dần theo ID và thực hiện phân trang (15 bản ghi/trang).
        $users = User::orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'status'  => true,
            'message' => 'Lấy danh sách người dùng thành công',
            // CHUẨN HÓA DỮ LIỆU: Sử dụng API Resource Collection để bóc tách/ẩn đi các trường nhạy cảm (như password, remember_token).
            'data'    => UserResource::collection($users),
            // METADATA PHÂN TRANG: Cung cấp thông tin cho phía Frontend xây dựng bộ chuyển trang.
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total()
            ]
        ]);
    }

    // =========================================================================
    // 📌 2. XEM CHI TIẾT NGƯỜI DÙNG (SHOW)
    // =========================================================================
    // VAI TRÒ: Lấy thông tin chi tiết của một người dùng cụ thể thông qua ID.
    public function show($id)
    {
        $user = User::find($id);

        // KHỐI LỆNH KIỂM TRA: Trả về lỗi 404 nếu không tìm thấy bản ghi nhằm tránh lỗi xử lý logic phía sau.
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Người dùng không tồn tại'
            ], 404);
        }

        return response()->json([
            'status' => true,
            // CHUẨN HÓA DỮ LIỆU: Khởi tạo instance đơn lẻ từ UserResource.
            'data'   => new UserResource($user)
        ]);
    }

    // =========================================================================
    // 📌 3. TẠO MỚI NGƯỜI DÙNG (STORE)
    // =========================================================================
    // VAI TRÒ: Cho phép Admin tạo một tài khoản người dùng mới trực tiếp từ hệ thống quản trị.
    public function store(Request $request)
    {
        // KHỐI VALIDATION THỦ CÔNG: Sử dụng Validator::make để chủ động kiểm soát cấu trúc JSON trả về khi gặp lỗi.
        // Các trường quan trọng như username, email, phone bắt buộc phải là duy nhất (unique) trong bảng 'users'.
        $validator = Validator::make(
            $request->all(),
            [
                'username'  => 'required|string|max:255|unique:users,username',
                'full_name' => 'nullable|string|max:255',
                'email'     => 'required|email|max:255|unique:users,email',
                'phone'     => 'required|string|max:20|unique:users,phone',
                'password'  => 'required|min:6',
                'role'      => 'nullable|in:admin,customer' // Giới hạn quyền nằm trong danh sách chỉ định
            ]
        );

        // XỬ LÝ LỖI VALIDATE: Nếu vi phạm bất kỳ ràng buộc nào, trả về danh sách lỗi cụ thể và mã 422.
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // THỰC THI TẠO TÀI KHOẢN:
        $user = User::create([
            'username'    => $request->username,
            'full_name'   => $request->full_name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'role'        => $request->role ?? 'customer', // Mặc định là tài khoản khách hàng nếu không truyền role
            'password'    => Hash::make($request->password), // Mã hóa mật khẩu bảo mật trước khi lưu
            'is_verified' => 1 // Mặc định kích hoạt trạng thái đã xác thực khi được tạo bởi Admin
        ]);

        // PHẢN HỒI HTTP 201: Mã trạng thái tiêu chuẩn biểu thị cho việc Resource đã được tạo thành công.
        return response()->json([
            'status'  => true,
            'message' => 'Đã tạo tài khoản thành công',
            'data'    => new UserResource($user)
        ], 201);
    }

    // =========================================================================
    // 📌 4. CẬP NHẬT THÔNG TIN NGƯỜI DÙNG (UPDATE)
    // =========================================================================
    // VAI TRÒ: Chỉnh sửa thông tin tài khoản dựa trên ID được chỉ định.
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Người dùng không tồn tại'
            ], 404);
        }

        // KHỐI VALIDATION CẬP NHẬT: Tất cả các trường chuyển về dạng 'nullable' (chỉ cập nhật trường nào được gửi lên).
        // KỸ THUẬT NGOẠI TRỪ (Ignore ID): Thêm `.$id` vào sau chuỗi quy tắc unique để hệ thống bỏ qua kiểm tra trùng lặp 
        // đối với chính tài khoản đang được sửa đổi này.
        $validator = Validator::make(
            $request->all(),
            [
                'username'  => 'nullable|string|max:255|unique:users,username,' . $id,
                'full_name' => 'nullable|string|max:255',
                'email'     => 'nullable|email|max:255|unique:users,email,' . $id,
                'phone'     => 'nullable|string|max:20|unique:users,phone,' . $id,
                'role'      => 'nullable|in:admin,customer',
                'password'  => 'nullable|min:6'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // LỌC DỮ LIỆU: Chỉ lấy các trường được phép cập nhật thông tin cơ bản từ request.
        $updateData = $request->only([
            'username',
            'full_name',
            'email',
            'phone',
            'role'
        ]);

        // XỬ LÝ MẬT KHẨU: Phương thức `filled()` kiểm tra nếu request có chứa mật khẩu và mật khẩu đó không rỗng, 
        // thì tiến hành mã hóa băm mới và đưa vào mảng cập nhật. Tránh việc ghi đè chuỗi trống vào cơ sở dữ liệu.
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Thực thi cập nhật dữ liệu hàng loạt (Mass Assignment) vào Database
        $user->update($updateData);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật thành công',
            'data'    => new UserResource($user)
        ]);
    }

    // =========================================================================
    // 📌 5. XÓA NGƯỜI DÙNG (DESTROY)
    // =========================================================================
    // VAI TRÒ: Xóa bỏ vĩnh viễn tài khoản người dùng ra khỏi hệ thống.
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Người dùng không tồn tại'
            ], 404);
        }

        // CƠ CHẾ PHÒNG THỦ (Defensive Programming): Biện pháp an toàn tuyệt đối, 
        // so sánh ID cần xóa với ID của Admin hiện tại đang đăng nhập (`Auth::id()`).
        // Mục đích: Ngăn chặn triệt để tình huống Admin vô tình tự xóa chính tài khoản của mình, gây mất quyền truy cập hệ thống.
        if ($user->id == Auth::id()) {
            return response()->json([
                'status'  => false,
                'message' => 'Không thể tự xóa bản thân'
            ], 400); // Mã lỗi 400 Bad Request
        }

        $user->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Đã xóa người dùng'
        ]);
    }
}