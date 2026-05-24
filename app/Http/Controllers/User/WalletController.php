<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Transaction;
use App\Models\Wallet;

class WalletController extends Controller
{
    /**
     * WALLET DASHBOARD
     */
    public function index()
    {
        $user = Auth::user();

        // Tạo ví nếu chưa có
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Lấy lịch sử giao dịch
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(5);

        return view('User.wallet.index', [
            'balance'      => $wallet->balance,
            'transactions' => $transactions
        ]);
    }

    /**
     * NẠP TIỀN
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:500000000'
        ], [
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.numeric'  => 'Số tiền không hợp lệ.',
            'amount.min'      => 'Nạp tối thiểu 10.000 VNĐ.',
            'amount.max'      => 'Số tiền vượt giới hạn cho phép.'
        ]);

        $user = Auth::user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        DB::beginTransaction();

        try {

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type'      => 'deposit',
                'amount'    => $request->amount,
                'status'    => 'pending'
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Yêu cầu nạp tiền đã được gửi. Vui lòng chờ hệ thống xác nhận.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Không thể tạo yêu cầu nạp tiền.'
            );
        }
    }

    /**
     * RÚT TIỀN
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount'    => 'required|numeric|min:10000|max:500000000',
            'bank_info' => 'required|string|max:255'
        ], [
            'amount.required'    => 'Vui lòng nhập số tiền.',
            'amount.numeric'     => 'Số tiền không hợp lệ.',
            'amount.min'         => 'Rút tối thiểu 10.000 VNĐ.',
            'bank_info.required' => 'Vui lòng nhập thông tin ngân hàng.'
        ]);

        $user = Auth::user();

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return back()->with(
                'error',
                'Không tìm thấy ví V-Pay.'
            );
        }

        // Kiểm tra số dư
        if ($wallet->balance < $request->amount) {
            return back()->with(
                'error',
                'Số dư không đủ để thực hiện giao dịch.'
            );
        }

        DB::beginTransaction();

        try {

            // Tạm khóa tiền
            $wallet->balance -= $request->amount;
            $wallet->save();

            Transaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'withdraw',
                'amount'         => $request->amount,
                'status'         => 'pending',
                'reference_code' => $request->bank_info // 🛠️ SỬA CHỮ 'note' THÀNH 'reference_code' CHO KHỚP DATABASE
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Yêu cầu rút tiền đã được gửi.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Không thể xử lý yêu cầu rút tiền.'
            );
        }
    }
}