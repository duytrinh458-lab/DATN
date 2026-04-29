<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Wallet;

class WalletController extends Controller
{
    public function index()
{
    $user = auth()->user();

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
        $request->validate([
            'amount' => 'required|numeric|min:1000'
        ]);

        $user = auth()->user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $wallet->balance += $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $request->amount
        ]);

        return back()->with('success', 'Nạp tiền thành công');
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000'
        ]);

        $user = auth()->user();

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return back()->with('error', 'Chưa có ví');
        }

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Không đủ tiền');
        }

        $wallet->balance -= $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'withdraw',
            'amount' => $request->amount
        ]);

        return back()->with('success', 'Rút tiền thành công');
    }
}