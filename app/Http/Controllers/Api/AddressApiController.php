<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressApiController extends Controller
{
    // Lấy danh sách địa chỉ của user
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => true,
            'data' => $addresses
        ]);
    }

    // Thêm địa chỉ mới
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'phone' => 'required',
            'street' => 'required',
            'city' => 'required'
        ]);

        $address = Address::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'street' => $request->street,
            'city' => $request->city,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thêm tọa độ thành công',
            'data' => $address
        ]);
    }
}