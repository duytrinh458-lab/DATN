<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller chịu trách nhiệm quản lý phân quyền và tài khoản người dùng (Users) trong hệ thống.
 * Xử lý các nghiệp vụ nâng cao bao gồm: Tự động tách chuỗi tạo Username độc bản, mã hóa mật khẩu bảo mật băm (Hash),
 * cập nhật trạng thái hoạt động và áp dụng cơ chế tự vệ ngăn chặn tài khoản Admin hiện hành tự xóa chính mình.
 */
class UserController extends Controller
{
    // ==========================================
    // 1. TRANG DANH SÁCH TÀI KHOẢN (INDEX)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: index()
     * Lấy ra danh sách toàn bộ tài khoản người dùng trong hệ thống để phục vụ công tác quản lý, phân quyền.
     */
    public function index()
    {
        // Sắp xếp theo ID giảm dần (tài khoản đăng ký mới nhất hiện lên đầu)
        // Phân trang (paginate) cố định 5 dòng mỗi trang giúp giao diện scannable, tránh tải nặng DB
        $users = User::orderBy('id', 'desc')->paginate(5);

        return view('Admin.users.index', compact('users'));
    }

    // ==========================================
    // 2. XEM CHI TIẾT TÀI KHOẢN (SHOW)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: show()
     * Hiển thị đầy đủ thông tin hồ sơ chi tiết của một người dùng dựa theo ID cụ thể.
     */
    public function show($id)
    {
        // Hàm findOrFail sẽ tự động trả về lỗi 404 nếu không tìm thấy ID người dùng tương ứng
        $user = User::findOrFail($id);

        return view('Admin.users.show', compact('user'));
    }

    // ==========================================
    // 3. TRANG GIAO DIỆN THÊM MỚI (CREATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: create()
     * Hiển thị màn hình Form để Admin chủ động khởi tạo một tài khoản mới (ví dụ tạo thêm tài khoản Admin phụ).
     */
    public function create()
    {
        return view('Admin.users.create');
    }

    // ==========================================
    // 4. XỬ LÝ LƯU TÀI KHOẢN MỚI (STORE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: store()
     * Tiếp nhận thông tin đăng ký, thực hiện thuật toán tự động sinh Username an toàn, 
     * tiến hành mã hóa mật khẩu và lưu bản ghi mới vào Database.
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI XÁC THỰC DỮ LIỆU ĐẦU VÀO chặt chẽ
        |--------------------------------------------------------------------------
        | - email: Không bắt buộc (nullable) nhưng nếu điền thì phải đúng định dạng và không được trùng.
        | - phone: Bắt buộc điền (required) và phải là duy nhất trong bảng users để làm kênh liên lạc.
        | - password: Bắt buộc, độ dài tối thiểu phải từ 6 ký tự trở lên để đảm bảo an toàn mật mã.
        | - role: Chỉ cho phép nhận 1 trong 2 phân quyền quy định: 'admin' hoặc 'customer'.
        */
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,customer'
        ]);

        /*
        |--------------------------------------------------------------------------
        | THUẬT TOÁN TỰ ĐỘNG KHỞI TẠO USERNAME ĐỘC BẢN
        |--------------------------------------------------------------------------
        | Ý nghĩa: Nếu người dùng có điền Email, hệ thống dùng hàm `explode()` cắt lấy chuỗi ký tự 
        | trước dấu '@' để làm Username (Ví dụ: "duytrinh@gmail.com" -> "duytrinh").
        | Ngược lại, nếu không điền email, hệ thống tự động sinh chuỗi định danh theo thời gian dạng: 'user' + timestamp.
        */
        $username = $validated['email']
            ? explode('@', $validated['email'])[0]
            : 'user' . time();

        // Rào chắn chống trùng: Kiểm tra lại trong DB, nếu vô tình trùng lặp username, 
        // hệ thống sẽ tự động nối thêm 3 chữ số ngẫu nhiên ngẫu nhiên ở đuôi (rand(100, 999)).
        if (User::where('username', $username)->exists()) {
            $username .= rand(100, 999);
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI MÃ HÓA BẢO MẬT VÀ KHỞI TẠO USER
        |--------------------------------------------------------------------------
        | - Hash::make(): Bắt buộc phải băm mật khẩu bằng thuật toán Bcrypt trước khi lưu vào DB,
        |   tuyệt đối không lưu mật khẩu dạng văn bản gốc (plain text) nhằm bảo mật an toàn thông tin.
        | - Các trạng thái mặc định: 'status' => 'active', 'is_verified' => 1 (Tài khoản kích hoạt sẵn).
        */
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

    // ==========================================
    // 5. XỬ LÝ CẬP NHẬT TRẠNG THÁI / VAI TRÒ (UPDATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: update()
     * Tiếp nhận yêu cầu chỉnh sửa quyền hạn hoặc trạng thái khóa/mở khóa tài khoản từ màn hình Admin.
     */
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

        // Điều hướng quay trở lại trang cũ (back) và đẩy thông báo cập nhật thành công lên màn hình
        return redirect()
            ->back()
            ->with('success', 'Cập nhật người dùng thành công');
    }

    // ==========================================
    // 6. XỬ LÝ XÓA TÀI KHOẢN (DESTROY)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: destroy()
     * Xóa bỏ tài khoản người dùng được chỉ định khỏi hệ thống. Có tích hợp cơ chế tự vệ hệ thống.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | CƠ CHẾ TỰ VỆ HỆ THỐNG (SYSTEM PROTECTION GUARD)
        |--------------------------------------------------------------------------
        | Ý nghĩa logic: Lấy ID của Admin đang đăng nhập hiện tại `auth()->id()` đối chiếu với ID tài khoản định xóa.
        | Nếu trùng nhau, lập tức chặn hành vi lại và trả về thông báo lỗi. 
        | Điều này ngăn chặn tình huống tai hại khi Admin vô tình tự xóa chính tài khoản mình đang dùng, 
        | dẫn đến việc hệ thống bị mất quyền quản trị tối cao và lỗi phiên đăng nhập ngay lập tức.
        */
        if (auth()->id() == $user->id) {
            return redirect()
                ->back()
                ->with('error', 'Không thể xoá tài khoản hiện tại');
        }

        // Thực thi lệnh xóa nếu vượt qua rào chắn an toàn phía trên
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã xoá user thành công');
    }
}