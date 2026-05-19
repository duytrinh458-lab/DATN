<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletApiController extends Controller
{
    // 🔐 CHECK AUTH CHUNG
    private function getUserId()
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Chưa đăng nhập hoặc token không hợp lệ'
            ], 401);
        }

        return $userId;
    }

    // 📌 API 48: Lấy số dư ví hiện tại
    public function balance()
    {
        $userId = $this->getUserId();
        if ($userId instanceof \Illuminate\Http\JsonResponse) return $userId;

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

    // 📌 API 49: Lịch sử giao dịch
    public function history()
    {
        $userId = $this->getUserId();
        if ($userId instanceof \Illuminate\Http\JsonResponse) return $userId;

        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json([
                'status' => true,
                'data' => []
            ]);
        }

        $transactions = DB::table('wallet_transactions')
            ->where('wallet_id', $wallet->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $transactions
        ]);
    }

    // 📌 API 50: Nạp tiền (ĐÃ VÁ: Chống hack tiền ảo, chuyển sang chờ Admin duyệt)
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ]);

        $userId = $this->getUserId();
        if ($userId instanceof \Illuminate\Http\JsonResponse) return $userId;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );

        // 💡 Chỉ tạo yêu cầu nạp tiền với trạng thái 'pending'
        DB::table('wallet_transactions')->insert([
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'reference_code' => 'DEP-' . time(),
            'status' => 'pending', 
            'created_at' => now()
        ]);

        // 💡 ĐÃ XÓA logic tự động cộng tiền tại đây để đảm bảo an toàn hệ thống

        return response()->json([
            'status' => true,
            'message' => 'Yêu cầu nạp tiền đã được ghi nhận. Vui lòng chờ Admin phê duyệt!',
            'data' => [
                'balance' => $wallet->balance // Số dư giữ nguyên cho đến khi được duyệt
            ]
        ]);
    }

    // 📌 API 51: xác nhận nạp tiền (mock)
    public function confirmDeposit(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Giao dịch đã được hệ thống xác nhận'
        ]);
    }
}