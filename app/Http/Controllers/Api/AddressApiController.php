<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressApiController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->get();
        return response()->json(['status' => true, 'data' => $addresses]);
    }

    public function store(Request $request)
    {
        // 🔥 FIX LỖI ĐỎ: Vẫn dùng 'ward' khớp DB
        $request->validate([
            'full_name' => 'required',
            'phone'    => 'required',
            'street'   => 'required',
            'district' => 'required',
            'ward'     => 'nullable',
            'province' => 'required',
        ]);

        $addressCount = Address::where('user_id', Auth::id())->count();
        $isDefault = ($addressCount == 0 || $request->is_default == 1) ? 1 : 0;

        if ($isDefault == 1 && $addressCount > 0) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        $address = Address::create([
            'user_id'    => Auth::id(),
            'full_name'  => $request->full_name,
            'phone'      => $request->phone,
            'street'     => $request->street,
            'district'   => $request->district,
            'ward'       => $request->ward,
            'province'   => $request->province,
            'is_default' => $isDefault,
        ]);

        return response()->json(['status' => true, 'message' => 'Thêm tọa độ nhận hàng thành công', 'data' => $address]);
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        if ($request->is_default == 1) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        // 💡 FIX LỖI XANH: Chỉ cập nhật đúng các trường này, tránh bị hacker chèn user_id
        $address->update($request->only([
            'full_name', 'phone', 'street', 'district', 'ward', 'province', 'is_default'
        ]));

        return response()->json(['status' => true, 'message' => 'Cập nhật địa chỉ thành công', 'data' => $address]);
    }

    public function destroy($id)
    {
        $address = Address::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        $address->delete();

        return response()->json(['status' => true, 'message' => 'Đã xóa địa chỉ']);
    }

    public function getShipFrom()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'name' => 'Kho Tổng Vanguard UAV',
                'address' => 'Khu Công Nghệ Cao Hòa Lạc, Thạch Thất, Hà Nội',
                'phone' => '19008888'
            ]
        ]);
    }

    public function getShipFee(Request $request)
    {
        $addressId = $request->query('address_id');
        
        if (!$addressId) {
            return response()->json(['status' => false, 'message' => 'Vui lòng chọn địa chỉ giao hàng'], 400);
        }

        $address = Address::where('id', $addressId)->where('user_id', Auth::id())->first();
        
        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Địa chỉ không hợp lệ'], 404);
        }

        $fee = 50000; 
        if (strpos(strtolower($address->province), 'hà nội') !== false || strpos(strtolower($address->ward), 'hà nội') !== false) {
            $fee = 30000;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'address_id' => $addressId,
                'destination' => $address->province,
                'shipping_fee' => $fee,
                'estimated_days' => ($fee == 30000) ? '1-2 ngày' : '3-5 ngày'
            ]
        ]);
    }
}