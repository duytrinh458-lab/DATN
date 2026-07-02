<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller chịu trách nhiệm quản lý toàn bộ vòng đời của các Danh mục sản phẩm (Categories).
 * Thực hiện các chức năng CRUD cơ bản bao gồm: Hiển thị danh sách, Thêm danh mục, Sửa danh mục và Xóa danh mục.
 * Đặc biệt có tích hợp chức năng chuyển đổi tên danh mục thành chuỗi đường dẫn tối ưu (Slug SEO).
 */
class CategoryController extends Controller
{
    // ==========================================
    // 1. TRANG DANH SÁCH DANH MỤC (INDEX)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: index()
     * Lấy ra danh sách các danh mục sản phẩm trong Database để hiển thị lên giao diện quản lý.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | TRUY VẤN DỮ LIỆU PHÂN TRANG
        |--------------------------------------------------------------------------
        | - orderBy('id', 'desc'): Sắp xếp các danh mục theo thứ tự ID giảm dần (danh mục mới nhất hiện lên đầu).
        | - paginate(5): Chia nhỏ danh sách dữ liệu, chỉ lấy đúng 5 danh mục trên mỗi trang để giao diện scannable, tránh quá tải.
        */
        $categories = Category::orderBy('id', 'desc')->paginate(5);

        // Trả về file giao diện hiển thị danh sách, truyền kèm biến $categories sang view
        return view('Admin.categories.index', compact('categories'));
    }

    // ==========================================
    // 2. TRANG GIAO DIỆN THÊM MỚI (CREATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: create()
     * Hiển thị màn hình chứa Form để Admin nhập tên danh mục muốn thêm mới.
     */
    public function create()
    {
        // Trả về file blade chứa form nhập liệu thêm mới danh mục
        return view('Admin.categories.create');
    }

    // ==========================================
    // 3. XỬ LÝ LƯU DANH MỤC MỚI (STORE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: store()
     * Tiếp nhận dữ liệu từ Form thêm mới gửi lên, kiểm tra tính hợp lệ và tiến hành ghi dữ liệu vào DB.
     * Biến quan trọng: $request (chứa toàn bộ dữ liệu ô nhập từ trình duyệt gửi về máy chủ).
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA TÍNH HỢP LỆ CỦA DỮ LIỆU (VALIDATION)
        |--------------------------------------------------------------------------
        | - 'name' => 'required': Bắt buộc Admin phải nhập tên danh mục, không được để trống.
        | - 'unique:categories,name': Tên danh mục này không được phép trùng lặp với bất kỳ tên danh mục nào đã có trong bảng 'categories'.
        */
        $request->validate([
            'name' => 'required|unique:categories,name'
        ]);

        /*
        |--------------------------------------------------------------------------
        | KHỐI TẠO BẢN GHI MỚI TRONG DATABASE
        |--------------------------------------------------------------------------
        | - 'name': Lưu tên danh mục do Admin nhập.
        | - 'slug': Sử dụng Helper `Str::slug()` để tự động chuyển đổi tên tiếng Việt có dấu thành dạng chuỗi không dấu, 
        |   ngăn cách bằng dấu gạch ngang (Ví dụ: "Linh Kiện UAV" -> "linh-kien-uav") giúp làm đường dẫn URL đẹp và chuẩn SEO.
        */
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        // Sau khi thêm thành công, điều hướng (redirect) về trang danh sách kèm thông báo thành công màu xanh (success)
        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Thêm thành công');
    }

    // ==========================================
    // 4. TRANG GIAO DIỆN CHỈNH SỬA (EDIT)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: edit()
     * Lấy thông tin chi tiết của một danh mục cụ thể dựa theo ID để đổ dữ liệu vào Form chỉnh sửa.
     * Biến quan trọng: $id (Giá trị ID của danh mục được truyền từ đường dẫn URL).
     */
    public function edit($id)
    {
        /*
        |--------------------------------------------------------------------------
        | TÌM KIẾM THEO ID HOẶC BÁO LỖI
        |--------------------------------------------------------------------------
        | Hàm `findOrFail($id)` sẽ tìm dòng dữ liệu có ID trùng khớp trong bảng categories.
        | - Nếu CÓ: Trả về thực thể danh mục đó để tiếp tục xử lý.
        | - Nếu KHÔNG CÓ: Lập tức dừng tiến trình và trả về trang lỗi 404 (Not Found) để bảo vệ hệ thống.
        */
        $category = Category::findOrFail($id);

        // Trả về view chỉnh sửa danh mục và truyền dữ liệu của danh mục vừa tìm được sang form
        return view('Admin.categories.edit', compact('category'));
    }

    // ==========================================
    // 5. XỬ LÝ CẬP NHẬT DANH MỤC (UPDATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: update()
     * Tiếp nhận dữ liệu từ Form chỉnh sửa, xác thực lại và cập nhật những thay đổi mới vào Database.
     */
    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA HỢP LỆ KHI CẬP NHẬT (NGOẠI TRỪ CHÍNH NÓ)
        |--------------------------------------------------------------------------
        | Ý nghĩa cú pháp: 'unique:categories,name,' . $id
        | Quy định tên danh mục không được trùng với các danh mục khác, nhưng hệ thống SẼ BỎ QUA không đối chiếu 
        | với ID hiện tại (giúp Admin giữ nguyên tên cũ bấm lưu form mà không bị báo lỗi trùng lặp).
        */
        $request->validate([
            'name' => 'required|unique:categories,name,' . $id
        ]);

        // Tìm thực thể danh mục cần sửa đổi trong Database theo ID, nếu không thấy báo lỗi 404
        $category = Category::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | KHỐI CẬP NHẬT DỮ LIỆU THAY ĐỔI
        |--------------------------------------------------------------------------
        | Tiến hành ghi đè tên mới và tạo lại một chuỗi Slug chuẩn SEO tương ứng với tên mới đó.
        */
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        // Điều hướng quay trở lại trang quản lý danh mục kèm theo thông báo cập nhật thành công
        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Cập nhật thành công');
    }

    // ==========================================
    // 6. XỬ LÝ XÓA DANH MỤC (DESTROY)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: destroy()
     * Thực hiện việc xóa bỏ hoàn toàn một danh mục được chỉ định ra khỏi Database.
     */
    public function destroy($id)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI THỰC THI LỆNH XÓA BẢN GHI
        |--------------------------------------------------------------------------
        | Tìm danh mục theo ID, nếu tìm thấy thì kích hoạt chuỗi lệnh `delete()` 
        | để xóa vĩnh viễn dòng dữ liệu đó ra khỏi bảng 'categories'.
        */
        Category::findOrFail($id)->delete();

        // Chuyển hướng về trang danh sách và phát ra thông báo đã xóa thành công
        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã xóa');
    }
}