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

        return view('User.profile.index', compact('user', 'addresses'));
    }

    /**
     * Cập nhật thông tin cơ bản (Tên, SĐT)
     */
    public function update(Request $request)
    {
        $user = User::find(Auth::id()); // Lấy instance từ Model để chắc chắn gọi được hàm update
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        $user->update([
            'name' => $request->name,
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

        // Lấy đối tượng user hiện tại từ Model
        $user = User::find(Auth::id());

        // 1. Tạo mới địa chỉ
        $address = Address::create([
            'user_id'    => $user->id,
            'street'     => $request->full_address,
            'full_name'  => $user->name ?? 'Người dùng', 
            'phone'      => $user->phone ?? '0000000000',
            'district'   => '', 
            'city'       => '',
            'province'   => '',
            'is_default' => 1, 
        ]);

        // 2. Cập nhật address_id vào bảng users để phục vụ đặt hàng
        $user->update([
            'address_id' => $address->id
        ]);

        return redirect()->back()->with('success', 'Đã lưu địa chỉ và thiết lập mặc định thành công!');
    }
}