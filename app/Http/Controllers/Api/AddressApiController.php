<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressApiController extends Controller
{
    // 📌 API 33: Lấy danh sách địa chỉ của user (GET /api/addresses)
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => true,
            'data' => $addresses
        ]);
    }

    // 📌 API 34: Thêm địa chỉ mới (POST /api/addresses)
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'phone' => 'required',
            'street' => 'required',
            'district' => 'required', // Bổ sung để khớp CSDL
            'city' => 'required',
            'province' => 'required'  // Bổ sung để khớp CSDL
        ]);

        // Xử lý logic: Nếu là địa chỉ đầu tiên, tự động set làm mặc định (is_default = 1)
        $addressCount = Address::where('user_id', Auth::id())->count();
        $isDefault = ($addressCount == 0 || $request->is_default == 1) ? 1 : 0;

        // Nếu user muốn đây là mặc định, các địa chỉ khác phải chuyển về 0
        if ($isDefault == 1 && $addressCount > 0) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        $address = Address::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'street' => $request->street,
            'district' => $request->district,
            'city' => $request->city,
            'province' => $request->province,
            'is_default' => $isDefault
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thêm tọa độ nhận hàng thành công',
            'data' => $address
        ]);
    }

    // 📌 API 35: Sửa địa chỉ (PUT /api/addresses/{id})
    public function update(Request $request, $id)
    {
        $address = Address::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        // Tương tự, nếu update thành mặc định thì gỡ mặc định các cái cũ
        if ($request->is_default == 1) {
            Address::where('user_id', Auth::id())->update(['is_default' => 0]);
        }

        // Hàm update($request->all()) sẽ tự động cập nhật các trường được gửi lên
        $address->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $address
        ]);
    }

    // 📌 API 36: Xóa địa chỉ (DELETE /api/addresses/{id})
    public function destroy($id)
    {
        $address = Address::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        $address->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa địa chỉ'
        ]);
    }

    // 📌 API 37: Lấy tọa độ kho hàng (GET /api/shipping/from)
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

    // 📌 API 38: Xem phí Ship (GET /api/shipping/fee?address_id=1)
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

        // Logic tính phí ship giả lập cho sinh viên:
        // Cùng thủ đô Hà Nội: 30k. Khác tỉnh: 50k.
        $fee = 50000; 
        if (strpos(strtolower($address->province), 'hà nội') !== false || strpos(strtolower($address->city), 'hà nội') !== false) {
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