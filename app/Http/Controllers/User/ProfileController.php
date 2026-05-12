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
    /**
     * Hiển thị trang hồ sơ
     */
    public function index()
    {
        $user = Auth::user();

        $addresses = Address::where('user_id', $user->id)->get();

        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

        return view('User.profile.index', compact(
            'user',
            'addresses',
            'defaultAddress'
        ));
    }

    /**
     * Cập nhật thông tin user + avatar
     */
    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        /**
         * UPLOAD AVATAR
         */
        if ($request->hasFile('avatar')) {

            // Xóa avatar cũ nếu tồn tại
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }

            $file = $request->file('avatar');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/avatars'), $fileName);

            $user->avatar = 'uploads/avatars/' . $fileName;
        }

        /**
         * UPDATE INFO
         */
        $user->full_name = $request->full_name;
        $user->phone = $request->phone;

        $user->save();

        return redirect()
            ->back()
            ->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Lưu địa chỉ mới
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'full_address' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        /**
         * Reset mặc định cũ
         */
        Address::where('user_id', $user->id)
            ->update([
                'is_default' => 0
            ]);

        /**
         * Tạo địa chỉ mới
         */
        Address::create([
            'user_id'    => $user->id,
            'street'     => $request->full_address,
            'full_name'  => $user->full_name ?? 'Người dùng',
            'phone'      => $user->phone ?? '',
            'district'   => '',
            'city'       => '',
            'province'   => '',
            'is_default' => 1,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Đã lưu địa chỉ thành công!');
    }

    /**
     * Đặt địa chỉ mặc định
     */
    public function setDefaultAddress($id)
    {
        $user = Auth::user();

        $address = Address::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        /**
         * Reset tất cả
         */
        Address::where('user_id', $user->id)
            ->update([
                'is_default' => 0
            ]);

        /**
         * Set mặc định mới
         */
        $address->update([
            'is_default' => 1
        ]);

        return redirect()
            ->back()
            ->with('success', 'Đã thay đổi địa chỉ mặc định!');
    }
}