<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 

class AdminController extends Controller
{
    public function dashboard()
    {
        $productCount = Product::count();
        $orderCount   = Order::count();
        $userCount    = User::count();
        $revenue      = Order::where('status', 'delivered')->sum('total');

        $bestProduct = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();

        $commentCount = DB::table('reviews')->count();

        return view('Admin.dashboard', compact('productCount', 'orderCount', 'userCount', 'revenue', 'bestProduct', 'commentCount'));
    }

    public function showQRSettings()
    {
        return view('Admin.settings.qr');
    }

    public function updateQR(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('qr_code')) {
            $image = $request->file('qr_code');
            $fileName = 'qr-demo.png'; 
            $image->move(public_path('images'), $fileName);
            return redirect()->back()->with('success', 'Đã cập nhật mã QR ngân hàng mới thành công!');
        }
        return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải ảnh lên.');
    }

    public function transactions()
    {
        $transactions = DB::table('wallet_transactions')
            ->join('wallets', 'wallet_transactions.wallet_id', '=', 'wallets.id')
            ->join('users', 'wallets.user_id', '=', 'users.id')
            ->select('wallet_transactions.*', 'users.full_name as user_name', 'users.email')
            ->orderByDesc('wallet_transactions.created_at')
            ->paginate(10);

        return view('Admin.transactions.index', compact('transactions'));
    }

    public function updateTransactionStatus(Request $request, $id)
    {
        $newStatus = $request->status;
        $transaction = DB::table('wallet_transactions')->where('id', $id)->first();

        if (!$transaction) return back()->with('error', 'Giao dịch không tồn tại!');
        if ($transaction->status == $newStatus) return back()->with('success', 'Trạng thái không thay đổi.');

        try {
            // 💡 ĐÃ FIX LOGIC SAI: Bọc trong DB::transaction để không bao giờ bị mất tiền
            DB::beginTransaction();

            if ($transaction->type == 'deposit') {
                if ($transaction->status == 'pending' && $newStatus == 'success') {
                    DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
                } 
                elseif ($transaction->status == 'success' && ($newStatus == 'pending' || $newStatus == 'failed')) {
                    DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
                }
            } 
            elseif ($transaction->type == 'withdraw') {
                if ($transaction->status == 'pending' && $newStatus == 'failed') {
                    DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
                }
                elseif ($transaction->status == 'failed' && $newStatus == 'pending') {
                    DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
                }
            }
            elseif ($transaction->type == 'payment') {
                if ($transaction->status == 'pending' && $newStatus == 'success') {
                    $wallet = DB::table('wallets')->where('id', $transaction->wallet_id)->first();
                    if ($wallet->balance >= $transaction->amount) {
                        DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
                        DB::table('payments')->where('order_id', function($q) use ($transaction) {
                            $q->select('id')->from('orders')->where('order_code', $transaction->reference_code)->limit(1);
                        })->update(['status' => 'paid', 'paid_at' => now()]);
                    } else {
                        DB::rollBack();
                        return back()->with('error', 'Khách hàng không đủ số dư!');
                    }
                }
                elseif ($transaction->status == 'success' && ($newStatus == 'pending' || $newStatus == 'failed')) {
                    DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
                    DB::table('payments')->where('order_id', function($q) use ($transaction) {
                        $q->select('id')->from('orders')->where('order_code', $transaction->reference_code)->limit(1);
                    })->update(['status' => 'pending', 'paid_at' => null]);
                }
            }

            DB::table('wallet_transactions')->where('id', $id)->update(['status' => $newStatus]);
            
            DB::commit();
            return back()->with('success', 'Đã cập nhật trạng thái và điều chỉnh số dư!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function refunds()
    {
        $refunds = DB::table('refunds')
            ->join('users', 'refunds.user_id', '=', 'users.id')
            ->join('orders', 'refunds.order_id', '=', 'orders.id')
            ->select('refunds.*', 'users.full_name as user_name', 'orders.order_code', 'orders.total')
            ->orderByDesc('refunds.created_at')
            ->paginate(10);

        return view('Admin.refunds.index', compact('refunds'));
    }

    public function updateRefundStatus(Request $request, $id)
    {
        $newStatus = $request->status; 
        $refund = DB::table('refunds')->where('id', $id)->first();

        if (!$refund) return back()->with('error', 'Không tìm thấy yêu cầu hoàn trả này!');
        if ($refund->status != 'pending') return back()->with('error', 'Yêu cầu này đã được xử lý trước đó!');

        if ($newStatus == 'approved') {
            $order = DB::table('orders')->where('id', $refund->order_id)->first();
            $wallet = DB::table('wallets')->where('user_id', $refund->user_id)->first();

            if ($wallet && $order) {
                DB::table('wallets')->where('id', $wallet->id)->increment('balance', $order->total);
                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $wallet->id,
                    'type'      => 'refund',
                    'amount'    => $order->total,
                    'status'    => 'success',
                    'created_at'=> now()
                ]);
                DB::table('orders')->where('id', $refund->order_id)->update(['status' => 'refunded']);
            }
        }

        DB::table('refunds')->where('id', $id)->update(['status' => $newStatus]);
        return back()->with('success', 'Đã xử lý yêu cầu hoàn trả!');
    }
}