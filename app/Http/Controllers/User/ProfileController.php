<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Address;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang hồ sơ và danh sách địa chỉ
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get();
        // Lấy riêng địa chỉ mặc định để dễ hiển thị
        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', 1)->first();

        return view('User.profile.index', compact('user', 'addresses', 'defaultAddress'));
    }

    /**
     * Cập nhật thông tin cơ bản (Tên, SĐT)
     */
    public function update(Request $request)
    {
        $user = User::find(Auth::id());
        
        $request->validate([
            'full_name' => 'required|string|max:255', // Đổi name thành full_name cho khớp DB
            'phone' => 'nullable|string|max:15',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
        ]);

        return redirect()->back()->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Lưu địa chỉ mới từ ô Textarea
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'full_address' => 'required|string|max:500',
        ]);

        $user = User::find(Auth::id());

        // 1. Chuyển tất cả địa chỉ cũ của user này thành không mặc định (is_default = 0)
        Address::where('user_id', $user->id)->update(['is_default' => 0]);

        // 2. Tạo địa chỉ mới và gán làm mặc định (is_default = 1)
        Address::create([
            'user_id'    => $user->id,
            'street'     => $request->full_address,
            'full_name'  => $user->full_name ?? 'Người dùng', // Khớp với cột full_name
            'phone'      => $user->phone ?? '0000000000',
            'district'   => '', 
            'city'       => '',
            'province'   => '',
            'is_default' => 1, 
        ]);

        // Đã XÓA phần update address_id vào bảng users để tránh lỗi DB

        return redirect()->back()->with('success', 'Đã lưu địa chỉ và thiết lập mặc định thành công!');
    }

    /**
     * Chuyển một địa chỉ cũ thành địa chỉ mặc định mới
     */
    public function setDefaultAddress($id)
    {
        $user = Auth::user();

        // 1. Tìm địa chỉ theo ID và đảm bảo nó thuộc về user đang đăng nhập (bảo mật)
        $address = Address::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // 2. Chuyển TẤT CẢ địa chỉ hiện tại của user này thành không mặc định (is_default = 0)
        Address::where('user_id', $user->id)->update(['is_default' => 0]);

        // 3. Chuyển địa chỉ vừa được click thành mặc định (is_default = 1)
        $address->update(['is_default' => 1]);

        return redirect()->back()->with('success', 'Đã thay đổi Tọa độ mặc định thành công!');
    }
}