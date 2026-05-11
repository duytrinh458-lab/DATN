<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth; // KHAI BÁO CÁI NÀY ĐỂ HẾT BÁO ĐỎ CHỮ USER

class WalletController extends Controller
{
    public function index()
    {
        // Dùng Auth::user() để IDE nhận diện chuẩn, hết gạch đỏ
        $user = Auth::user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $balance = $wallet->balance;

        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(5);

        return view('User.wallet.index', compact('balance', 'transactions'));
    }

    public function deposit(Request $request)
    {
        // 1. Kiểm tra đầu vào, thêm thông báo chuẩn
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ], [
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.numeric'  => 'Số tiền phải là số hợp lệ.',
            'amount.min'      => 'Số tiền nạp tối thiểu là 10.000 VNĐ.'
        ]);

        $user = Auth::user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // 2. KHÔNG CỘNG TIỀN VÀO $wallet->balance NỮA
        // Xóa 2 dòng $wallet->balance +=... và $wallet->save();

        // 3. TẠO GIAO DỊCH VỚI TRẠNG THÁI 'PENDING'
        Transaction::create([
            'wallet_id' => $wallet->id,
            'type'      => 'deposit',
            'amount'    => $request->amount,
            'status'    => 'pending' // Cột này cần có trong DB của ông nhé
        ]);

        return back()->with('success', 'Yêu cầu nạp tiền đã được gửi. Vui lòng chờ Admin phê duyệt!');
    }

    public function withdraw(Request $request)
    {
        // Rút tiền cũng đưa về pending để Admin duyệt chuyển khoản thủ công
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là 10.000 VNĐ.'
        ]);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return back()->with('error', 'Không tìm thấy ví V-Pay của bạn.');
        }

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Số dư trong ví không đủ để rút.');
        }

        // Tạm trừ tiền trong ví để khách không lấy tiền đó mua hàng
        $wallet->balance -= $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type'      => 'withdraw',
            'amount'    => $request->amount,
            'status'    => 'pending' // Chờ Admin xác nhận đã chuyển tiền
        ]);

        return back()->with('success', 'Yêu cầu rút tiền đã được gửi. Hệ thống sẽ xử lý trong 24h.');
    }
}