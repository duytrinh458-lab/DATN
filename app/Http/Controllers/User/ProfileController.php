<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Address;

class ProfileController extends Controller
{
    // =========================================================================
    // 1. TRANG HỒ SƠ CÁ NHÂN & SỔ ĐỊA CHỈ (INDEX)
    // =========================================================================
    /**
     * Chức năng: Hiển thị thông tin tài khoản, danh sách các địa chỉ đã lưu 
     * và xác định đâu là địa chỉ nhận hàng mặc định.
     */
    public function index()
    {
        $user = Auth::user();

        // Lấy danh sách toàn bộ địa chỉ của khách hàng này, xếp cái mới thêm lên đầu
        $addresses = Address::where('user_id', $user->id)
            ->latest('id')
            ->get();

        // Bộ lọc nhanh: Tìm ra đúng 1 địa chỉ duy nhất đang được chọn làm mặc định (is_default = 1)
        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

        return view('User.profile.index', compact('user', 'addresses', 'defaultAddress'));
    }

    // =========================================================================
    // 2. CẬP NHẬT THÔNG TIN & XỬ LÝ ẢNH ĐẠI DIỆN (UPDATE PROFILE)
    // =========================================================================
    /**
     * Chức năng: Thay đổi Họ tên, Số điện thoại và xử lý nén/đổi/xóa ảnh đại diện (Avatar).
     * Cam kết an toàn hạ tầng: Tự động dọn dẹp file ảnh cũ khỏi ổ đĩa để tránh rác server.
     */
    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        // Kiểm định dữ liệu: Họ tên bắt buộc, ảnh đại diện phải đúng định dạng và dung lượng dưới 2MB
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // --- CƠ CHẾ 1: XỬ LÝ XÓA ẢNH ĐẠI DIỆN CHỦ ĐỘNG ---
        if ($request->input('delete_avatar') == 1) {
            // Nếu trên bộ nhớ server đang có file ảnh cũ, tiến hành xóa file vật lý để tiết kiệm dung lượng
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }
            // Gỡ bỏ đường dẫn ảnh trong database (chuyển về null để dùng ảnh mặc định)
            $user->avatar = null;
        }

        // --- CƠ CHẾ 2: UPLOAD ẢNH ĐẠI DIỆN MỚI ---
        if ($request->hasFile('avatar')) {
            // [DỌN DẸP LỊCH SỬ]: Khách đổi ảnh mới thì phải xóa ảnh cũ ngay để tránh lưu file rác vô tận
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }

            $file = $request->file('avatar');
            // Đóng dấu thời gian (Timestamp) vào tên file để đảm bảo tên ảnh là duy nhất, không bị ghi đè
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Di chuyển file ảnh vào thư mục lưu trữ công khai trên server
            $file->move(public_path('uploads/avatars'), $fileName);

            // Lưu lại đường dẫn tương đối vào database để phục vụ việc hiển thị ở giao diện công cộng
            $user->avatar = 'uploads/avatars/' . $fileName;
        }

        // Lưu thông tin cơ bản
        $user->full_name = $request->full_name;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    // =========================================================================
    // 3. QUẢN LÝ SỔ ĐỊA CHỈ GIAO HÀNG (ADDRESS MANAGEMENT)
    // =========================================================================
    
    /**
     * Chức năng: Thêm mới một địa chỉ giao hàng và tự động đặt nó làm mặc định.
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'street'    => 'required|string|max:255',
            'district'  => 'nullable|string|max:255',
            'ward'      => 'nullable|string|max:255',
            'province'  => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        // [LOGIC ĐỘC QUYỀN]: Hạ toàn bộ các địa chỉ cũ xuống thành địa chỉ phụ (is_default = 0)
        // để chuẩn bị nhường vị trí mặc định cho địa chỉ sắp tạo.
        Address::where('user_id', $user->id)->update(['is_default' => 0]);

        // Tạo mới địa chỉ và thiết lập làm địa chỉ mặc định (is_default = 1)
        Address::create([
            'user_id'    => $user->id,
            'street'     => $request->street,
            'district'   => $request->district,
            'ward'       => $request->ward,
            'province'   => $request->province,
            // Nếu điền tên/sđt riêng thì lấy, nếu để trống hệ thống tự động đồng bộ theo thông tin chủ tài khoản
            'full_name'  => $request->full_name ?? $user->full_name,
            'phone'      => $request->phone ?? $user->phone,
            'is_default' => 1,
        ]);

        return redirect()->back()->with('success', 'Đã lưu và đặt địa chỉ mới làm mặc định!');
    }

    /**
     * Chức năng: Thay đổi thủ công địa chỉ mặc định theo yêu cầu của khách hàng.
     */
    public function setDefaultAddress($id)
    {
        $user = Auth::user();

        // Kiểm tra an toàn: Đảm bảo địa chỉ này tồn tại và thuộc về chính tài khoản đang yêu cầu
        $address = Address::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // Bước 1: Gỡ bỏ trạng thái mặc định của tất cả địa chỉ cũ
        Address::where('user_id', $user->id)->update(['is_default' => 0]);

        // Bước 2: Kích hoạt địa chỉ được chọn lên làm mặc định
        $address->update(['is_default' => 1]);

        return redirect()->back()->with('success', 'Đã thay đổi địa chỉ mặc định!');
    }

    /**
     * Chức năng: Trả về trang chỉnh sửa thông tin của một địa chỉ cụ thể.
     */
    public function editAddress($id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        return view('User.profile.edit_address', compact('address'));
    }

    /**
     * Chức năng: API trả về dữ liệu JSON của địa chỉ (Phục vụ gọi ngầm Ajax đổ dữ liệu vào Form Modal)
     */
    public function getAddressJson($id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        return response()->json([
            'id'         => $address->id,
            'full_name'  => $address->full_name,
            'phone'      => $address->phone,
            'province'   => $address->province,
            'district'   => $address->district,
            'ward'       => $address->ward,
            'street'     => $address->street,
            'is_default' => $address->is_default,
        ]);
    }

    /**
     * Chức năng: Lưu lại các thông tin địa chỉ sau khi khách hàng sửa đổi.
     */
    public function updateAddress(Request $request, $id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'street'    => 'required|string|max:255',
            'district'  => 'nullable|string|max:255',
            'ward'      => 'nullable|string|max:255',
            'province'  => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ]);

        // Sử dụng $request->only để bọc lọc an toàn, chỉ cập nhật đúng các trường được phép sửa
        $address->update($request->only(['street', 'district', 'ward', 'province', 'full_name', 'phone']));

        return redirect()->route('user.profile.index')->with('success', 'Địa chỉ đã được cập nhật!');
    }

    /**
     * Chức năng: Xóa địa chỉ khỏi danh sách.
     * Cơ chế phòng thủ UX: Tuyệt đối không cho xóa nếu khách chỉ còn duy nhất 1 địa chỉ.
     */
    public function destroyAddress($id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        // Đếm xem ngoài địa chỉ sắp xóa ra, khách còn địa chỉ nào khác dự phòng không
        $otherAddresses = Address::where('user_id', $user->id)
            ->where('id', '!=', $id)
            ->count();

        // [BẪY NGHIỆP VỤ UX]: Nếu số lượng địa chỉ còn lại bằng 0, chặn ngay hành vi xóa
        // Mục đích: Ép khách phải giữ lại ít nhất 1 địa chỉ để hệ thống có dữ liệu tính phí ship khi họ vào trang Checkout mua UAV
        if ($otherAddresses <= 0) {
            return redirect()->route('user.profile.index')->with('error', 'Bạn cần có ít nhất 1 địa chỉ!');
        }

        // Nếu còn địa chỉ khác, tiến hành xóa bình thường
        $address->delete();

        return redirect()->route('user.profile.index')->with('success', 'Đã xóa địa chỉ thành công!');
    }
}