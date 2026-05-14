<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Lấy danh sách địa chỉ của user
        $addresses = Address::where('user_id', $user->id)->get();

        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

        return view('User.profile.index', compact('user','addresses','defaultAddress'));
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $fileName);
            $user->avatar = 'uploads/avatars/' . $fileName;
        }

        $user->full_name = $request->full_name;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'street'    => 'required|string|max:255',
            'district'  => 'nullable|string|max:255',
            'city'      => 'nullable|string|max:255',
            'province'  => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        // Reset tất cả địa chỉ cũ về không mặc định
        Address::where('user_id', $user->id)->update(['is_default' => 0]);

        // Tạo địa chỉ mới và gán mặc định
        Address::create([
            'user_id'    => $user->id,
            'street'     => $request->street,
            'district'   => $request->district,
            'city'       => $request->city,
            'province'   => $request->province,
            'full_name'  => $request->full_name ?? $user->full_name,
            'phone'      => $request->phone ?? $user->phone,
            'is_default' => 1,
        ]);

        return redirect()->back()->with('success', 'Đã lưu và đặt địa chỉ mới làm mặc định!');
    }

    public function setDefaultAddress($id)
    {
        $user = Auth::user();
        $address = Address::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        Address::where('user_id', $user->id)->update(['is_default' => 0]);
        $address->update(['is_default' => 1]);

        return redirect()->back()->with('success', 'Đã thay đổi địa chỉ mặc định!');
    }

    public function editAddress($id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        return view('User.profile.edit_address', compact('address'));
    }

    public function updateAddress(Request $request, $id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'street'    => 'required|string|max:255',
            'district'  => 'nullable|string|max:255',
            'city'      => 'nullable|string|max:255',
            'province'  => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ]);

        $address->update($request->only([
            'street', 'district', 'city', 'province', 'full_name', 'phone'
        ]));

        return redirect()->route('user.profile.index')->with('success', 'Địa chỉ đã được cập nhật!');
    }

    public function destroyAddress($id)
{
    $user = Auth::user();
    $address = Address::where('user_id', $user->id)->findOrFail($id);

    $orders = $address->orders;
    $hasActiveOrders = $orders->where('status', '!=', 'cancelled')->count() > 0;

    if ($hasActiveOrders) {
        return redirect()->route('user.profile.index')
            ->with('error', 'Không thể xóa địa chỉ vì đang được sử dụng trong đơn hàng chưa hủy.');
    }

    // Xóa hẳn bản ghi
    $address->delete();

    return redirect()->route('user.profile.index')
        ->with('success', 'Địa chỉ đã được xóa!');
}

}
