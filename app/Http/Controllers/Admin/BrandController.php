<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller chịu trách nhiệm quản lý toàn bộ vòng đời của các Thương hiệu (Brands) trong hệ thống.
 * Thực hiện các chức năng CRUD cơ bản bao gồm: Xem danh sách, Thêm mới, Chỉnh sửa và Xóa thương hiệu sản phẩm.
 */
class BrandController extends Controller
{
    /**
     * VAI TRÒ CỦA METHOD: index()
     * Lấy danh sách các thương hiệu hiện có trong hệ thống và hiển thị ra giao diện quản lý cho Admin.
     */
    public function index()
    {
        // Lấy danh sách thương hiệu, sắp xếp theo ID giảm dần (thương hiệu mới tạo sẽ lên đầu)
        // Phân trang (paginate): chỉ lấy đúng 5 mục trên mỗi trang để giao diện gọn gàng, tránh tải quá nhiều dữ liệu cùng lúc
        $brands = Brand::orderBy('id', 'desc')->paginate(5);

        // Trả về file giao diện hiển thị danh sách, truyền kèm biến dữ liệu $brands sang view
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * VAI TRÒ CỦA METHOD: create()
     * Hiển thị màn hình chứa Form nhập liệu để Admin có thể thêm một thương hiệu mới.
     */
    public function create()
    {
        // Trả về file giao diện (blade view) chứa form điền tên và tải logo thương hiệu
        return view('admin.brands.create');
    }

    /**
     * VAI TRÒ CỦA METHOD: store()
     * Tiếp nhận và xử lý dữ liệu gửi lên từ form thêm mới: Kiểm tra tính hợp lệ, lưu tệp tin ảnh logo và chèn vào Database.
     * Biến quan trọng: $request (đối tượng chứa toàn bộ thông tin văn bản và tệp tin do Admin nhập từ form).
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI KIỂM TRA DỮ LIỆU ĐẦU VÀO (VALIDATION)
        |--------------------------------------------------------------------------
        | Mục đích: Đảm bảo dữ liệu gửi lên hệ thống phải sạch, an toàn và đúng cấu trúc.
        | - name: Bắt buộc phải điền ('required'), tối đa 100 ký tự ('max:100'), không được trùng tên trong bảng brands ('unique:brands,name').
        | - logo: Không bắt buộc ('nullable'), nếu có thì bắt buộc là ảnh ('image'), đúng định dạng tệp và nặng tối đa 2MB ('max:2048').
        */
        $request->validate([
            'name' => 'required|max:100|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Mặc định ban đầu gán đường dẫn logo bằng null (trường hợp Admin không chọn ảnh)
        $logo = null;

        /*
        |--------------------------------------------------------------------------
        | KHỐI XỬ LÝ TẢI FILE ẢNH LOGO (UPLOAD FILE)
        |--------------------------------------------------------------------------
        | Điều kiện: Nếu trong form gửi lên có đính kèm tệp tin hợp lệ tại ô nhập 'logo'
        */
        if ($request->hasFile('logo')) {
            // Lưu file ảnh vào thư mục 'brands' nằm trong đĩa lưu trữ công khai 'public'
            // Hệ thống sẽ tự động băm tên file thành một chuỗi ký tự ngẫu nhiên duy nhất để tránh trùng tên ảnh trên máy chủ
            $logo = $request->file('logo')
                            ->store('brands', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI LƯU DỮ LIỆU VÀO DATABASE
        |--------------------------------------------------------------------------
        | Tạo một dòng dữ liệu mới trong bảng 'brands' thông qua cơ chế Mass Assignment của Eloquent ORM
        */
        Brand::create([
            'name' => $request->name,
            'logo' => $logo,
        ]);

        // Điều hướng Admin quay trở lại trang danh sách thương hiệu kèm theo thông báo thành công màu xanh (success)
        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Thêm thương hiệu thành công');
    }

    /**
     * VAI TRÒ CỦA METHOD: edit()
     * Hiển thị màn hình chứa Form chỉnh sửa thông tin cho một thương hiệu cụ thể nào đó.
     * Biến quan trọng: Brand $brand (Áp dụng cơ chế Route Model Binding, Laravel tự động tìm thực thể thương hiệu trong DB dựa vào ID trên thanh URL).
     */
    public function edit(Brand $brand)
    {
        // Trả về view chỉnh sửa, đồng thời truyền toàn bộ thông tin của thương hiệu cần sửa sang giao diện
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * VAI TRÒ CỦA METHOD: update()
     * Tiếp nhận và xử lý dữ liệu gửi lên từ form chỉnh sửa: Kiểm tra hợp lệ, thay thế ảnh logo cũ (nếu có) và cập nhật Database.
     */
    public function update(Request $request, Brand $brand)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI KIỂM TRA DỮ LIỆU ĐẦU VÀO KHI CẬP NHẬT
        |--------------------------------------------------------------------------
        | Ý nghĩa đoạn code: 'unique:brands,name,' . $brand->id
        | Tên thương hiệu không được trùng với các thương hiệu khác, nhưng hệ thống SẼ BỎ QUA không đối chiếu 
        | với ID của chính thương hiệu hiện tại (giúp Admin có thể bấm lưu form mà không bị hệ thống báo lỗi trùng tên với chính nó).
        */
        $request->validate([
            'name' => 'required|max:100|unique:brands,name,' . $brand->id,
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Ban đầu gán giữ nguyên đường dẫn ảnh logo cũ đang có trong Database
        $logo = $brand->logo;

        /*
        |--------------------------------------------------------------------------
        | KHỐI XỬ LÝ THAY THẾ ẢNH LOGO MỚI
        |--------------------------------------------------------------------------
        | Điều kiện: Nếu Admin bấm chọn một tệp tin ảnh mới để thay đổi logo hiện tại
        */
        if ($request->hasFile('logo')) {

            // Kiểm tra an toàn: Nếu trong DB đã có đường dẫn ảnh cũ VÀ tệp tin đó thực sự tồn tại trong thư mục lưu trữ vật lý
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                // Tiến hành XÓA file ảnh cũ này đi để giải phóng dung lượng ổ cứng máy chủ, tránh tích tụ rác file
                Storage::disk('public')->delete($brand->logo);
            }

            // Tiến hành lưu tệp tin ảnh logo mới vào thư mục 'brands' và gán lại đường dẫn mới vào biến $logo
            $logo = $request->file('logo')
                            ->store('brands', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI CẬP NHẬT DỮ LIỆU VÀO DATABASE
        |--------------------------------------------------------------------------
        */
        $brand->update([
            'name' => $request->name,
            'logo' => $logo,
        ]);

        // Điều hướng quay về trang danh sách thương hiệu kèm thông báo cập nhật thành công
        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công');
    }

    /**
     * VAI TRÒ CỦA METHOD: destroy()
     * Thực hiện xóa hoàn toàn một thương hiệu được chỉ định ra khỏi hệ thống.
     */
    public function destroy(Brand $brand)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI DỌN SẠCH FILE ẢNH TRƯỚC KHI XÓA BẢN GHI
        |--------------------------------------------------------------------------
        | Mục đích: Tránh tình trạng để lại "file mồ côi" trên máy chủ (Dữ liệu tên thương hiệu trong 
        | Database đã bị xóa mất, nhưng tệp tin hình ảnh vẫn nằm lại vĩnh viễn trong ổ đĩa gây lãng phí tài nguyên).
        */
        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        // Thực thi lệnh xóa (DELETE) dòng dữ liệu thương hiệu này trong Database
        $brand->delete();

        // Quay trở lại trang danh sách thương hiệu và gửi kèm thông báo xóa thành công ra màn hình
        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Xóa thương hiệu thành công');
    }
}