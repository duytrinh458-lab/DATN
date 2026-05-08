<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Thêm DB để ghi log giao dịch

class WalletApiController extends Controller
{
    // 📌 API 48: Lấy số dư ví hiện tại (GET /api/get_current_balance)
    public function balance()
    {
        $userId = Auth::id();
        
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

    // 📌 API 49: Xem lịch sử giao dịch (GET /api/get_balance_history)
    public function history()
    {
        $userId = Auth::id();
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json(['status' => true, 'data' => []]);
        }

        // Lấy danh sách giao dịch từ bảng wallet_transactions
        $transactions = DB::table('wallet_transactions')
            ->where('wallet_id', $wallet->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $transactions
        ]);
    }

    // 📌 API 50: Tạo lệnh nạp tiền (POST /api/create_deposit_request)
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

        // Ghi lại lịch sử nạp tiền vào bảng wallet_transactions
        DB::table('wallet_transactions')->insert([
            'wallet_id' => $wallet->id,
            'type' => 'deposit', // Loại: Nạp tiền
            'amount' => $request->amount,
            'reference_code' => 'DEP-' . time(), // Mã tham chiếu nạp tiền
            'status' => 'success', // Vì mình đang làm test nên cho success luôn
            'created_at' => now()
        ]);

        // Cộng tiền vào ví
        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json([
            'status' => true,
            'message' => 'Nạp tiền vào ví V-Pay thành công!',
            'data' => [
                'balance' => $wallet->balance
            ]
        ]);
    }

    // 📌 API 51: Xác nhận nạp tiền (POST /api/confirm_deposit)
    // Dùng để giả lập việc xác nhận sau khi nạp qua ngân hàng/momo
    public function confirmDeposit(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Giao dịch đã được hệ thống xác nhận'
        ]);
    }
}