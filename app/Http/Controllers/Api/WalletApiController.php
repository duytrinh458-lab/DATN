<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class WalletApiController extends Controller
{
    // Lấy số dư ví hiện tại
    public function balance()
    {
        $userId = Auth::id();
        // Tìm ví, nếu chưa có thì tạo mới ví với 0 đồng
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );

        return response()->json([
            'status' => true,
            'data' => [
                'balance' => $wallet->balance
            ]
        ]);
    }

    // Khách nạp tiền vào ví V-Pay
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ]);

        $userId = Auth::id();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );

        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json([
            'status' => true,
            'message' => 'Nạp tiền thành công!',
            'data' => [
                'balance' => $wallet->balance
            ]
        ]);
    }
}