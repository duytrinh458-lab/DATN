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
    /*
    |--------------------------------------------------------------------------
    | PROFILE PAGE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | ADDRESS LIST
        |--------------------------------------------------------------------------
        */
        $addresses = Address::where('user_id', $user->id)

            ->latest('id')

            ->get();

        /*
        |--------------------------------------------------------------------------
        | DEFAULT ADDRESS
        |--------------------------------------------------------------------------
        */
        $defaultAddress = Address::where('user_id', $user->id)

            ->where('is_default', 1)

            ->first();

        return view(
            'User.profile.index',
            compact(
                'user',
                'addresses',
                'defaultAddress'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        /*
        |--------------------------------------------------------------------------
        | XỬ LÝ XÓA AVATAR (MỚI THÊM VÀO ĐÂY)
        |--------------------------------------------------------------------------
        */
        if ($request->input('delete_avatar') == 1) {
            // Nếu có file ảnh cũ trong thư mục, tiến hành xóa file vật lý đi
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }
            // Đặt lại trường avatar trong database thành null
            $user->avatar = null;
        }

        /*
        |--------------------------------------------------------------------------
        | UPLOAD AVATAR
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('avatar')) {

            /*
            |--------------------------------------------------------------------------
            | DELETE OLD AVATAR
            |--------------------------------------------------------------------------
            */
            if (
                $user->avatar &&
                File::exists(public_path($user->avatar))
            ) {

                File::delete(
                    public_path($user->avatar)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE NEW AVATAR
            |--------------------------------------------------------------------------
            */
            $file = $request->file('avatar');

            $fileName =
                time()
                . '_'
                . $file->getClientOriginalName();

            $file->move(
                public_path('uploads/avatars'),
                $fileName
            );

            $user->avatar =
                'uploads/avatars/' . $fileName;
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE USER
        |--------------------------------------------------------------------------
        */
        $user->full_name = $request->full_name;

        $user->phone = $request->phone;

        $user->save();

        return redirect()

            ->back()

            ->with(
                'success',
                'Cập nhật thông tin cá nhân thành công!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ADDRESS
    |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | RESET DEFAULT
        |--------------------------------------------------------------------------
        */
        Address::where('user_id', $user->id)

            ->update([
                'is_default' => 0
            ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE ADDRESS
        |--------------------------------------------------------------------------
        */
        Address::create([

            'user_id'    => $user->id,

            'street'     => $request->street,

            'district'   => $request->district,

            'ward'       => $request->ward,

            'province'   => $request->province,

            'full_name'  =>
                $request->full_name
                ?? $user->full_name,

            'phone'      =>
                $request->phone
                ?? $user->phone,

            'is_default' => 1,
        ]);

        return redirect()

            ->back()

            ->with(
                'success',
                'Đã lưu và đặt địa chỉ mới làm mặc định!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SET DEFAULT ADDRESS
    |--------------------------------------------------------------------------
    */
    public function setDefaultAddress($id)
    {
        $user = Auth::user();

        $address = Address::where('id', $id)

            ->where('user_id', $user->id)

            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | RESET ALL
        |--------------------------------------------------------------------------
        */
        Address::where('user_id', $user->id)

            ->update([
                'is_default' => 0
            ]);

        /*
        |--------------------------------------------------------------------------
        | SET DEFAULT
        |--------------------------------------------------------------------------
        */
        $address->update([
            'is_default' => 1
        ]);

        return redirect()

            ->back()

            ->with(
                'success',
                'Đã thay đổi địa chỉ mặc định!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT ADDRESS PAGE
    |--------------------------------------------------------------------------
    */
    public function editAddress($id)
    {
        $user = Auth::user();

        $address = Address::where('user_id', $user->id)

            ->findOrFail($id);

        return view(
            'User.profile.edit_address',
            compact('address')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET ADDRESS JSON
    |--------------------------------------------------------------------------
    */
    public function getAddressJson($id)
    {
        $user = Auth::user();

        $address = Address::where('user_id', $user->id)

            ->findOrFail($id);

        return response()->json([

            'id' => $address->id,

            'full_name' => $address->full_name,

            'phone' => $address->phone,

            'province' => $address->province,

            'district' => $address->district,

            'ward' => $address->ward,

            'street' => $address->street,

            'is_default' => $address->is_default,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ADDRESS
    |--------------------------------------------------------------------------
    */
    public function updateAddress(Request $request, $id)
    {
        $user = Auth::user();

        $address = Address::where('user_id', $user->id)

            ->findOrFail($id);

        $request->validate([

            'street'    => 'required|string|max:255',

            'district'  => 'nullable|string|max:255',

            'ward'      => 'nullable|string|max:255',

            'province'  => 'nullable|string|max:255',

            'full_name' => 'nullable|string|max:255',

            'phone'     => 'nullable|string|max:20',
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */
        $address->update(

            $request->only([

                'street',

                'district',

                'ward',

                'province',

                'full_name',

                'phone'
            ])
        );

        return redirect()

            ->route('user.profile.index')

            ->with(
                'success',
                'Địa chỉ đã được cập nhật!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ADDRESS
    |--------------------------------------------------------------------------
    */
    public function destroyAddress($id)
    {
        $user = Auth::user();

        $address = Address::where('user_id', $user->id)

            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | KHÔNG CHO XÓA NẾU CHỈ CÒN 1 ĐỊA CHỈ
        |--------------------------------------------------------------------------
        */
        $otherAddresses = Address::where('user_id', $user->id)

            ->where('id', '!=', $id)

            ->count();

        if ($otherAddresses <= 0) {

            return redirect()

                ->route('user.profile.index')

                ->with(
                    'error',
                    'Bạn cần có ít nhất 1 địa chỉ!'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | XÓA ĐỊA CHỈ
        |--------------------------------------------------------------------------
        */
        $address->delete();

        return redirect()

            ->route('user.profile.index')

            ->with(
                'success',
                'Đã xóa địa chỉ thành công!'
            );
    }
}