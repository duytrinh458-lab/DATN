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
    // ==========================================
    // 1. TRANG TỔNG QUAN (DASHBOARD)
    // ==========================================
    public function dashboard()
    {
        $productCount = Product::count();
        $orderCount   = Order::count();
        $userCount    = User::count();

        $revenue = Order::where('status', 'delivered')->sum('total');

        $bestProduct = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();

        $commentCount = DB::table('reviews')->count();

        return view('Admin.dashboard', compact('productCount', 'orderCount', 'userCount', 'revenue', 'bestProduct', 'commentCount'));
    }

    // ==========================================
    // 2. QUẢN LÝ CẤU HÌNH QR V-PAY
    // ==========================================
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

    // ==========================================
    // 3. QUẢN LÝ GIAO DỊCH VÍ V-PAY
    // ==========================================
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

        if (!$transaction) {
            return back()->with('error', 'Giao dịch không tồn tại!');
        }

        $oldStatus = $transaction->status;

        if ($oldStatus == $newStatus) {
            return back()->with('success', 'Trạng thái không thay đổi.');
        }

        // Xử lý biến động số dư ví khi Admin thay đổi trạng thái
        if ($transaction->type == 'deposit') {
            if ($oldStatus == 'pending' && $newStatus == 'success') {
                DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
            } 
            elseif ($oldStatus == 'success' && ($newStatus == 'pending' || $newStatus == 'failed')) {
                DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
            }
        } 
        elseif ($transaction->type == 'withdraw') {
            if ($oldStatus == 'pending' && $newStatus == 'failed') {
                DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
            }
            elseif ($oldStatus == 'failed' && $newStatus == 'pending') {
                DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
            }
        }
        // XỬ LÝ KHI ADMIN DUYỆT ĐƠN HÀNG THANH TOÁN BẰNG VÍ
        elseif ($transaction->type == 'payment') {
            if ($oldStatus == 'pending' && $newStatus == 'success') {
                $wallet = DB::table('wallets')->where('id', $transaction->wallet_id)->first();
                if ($wallet->balance >= $transaction->amount) {
                    DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
                    
                    DB::table('payments')->where('order_id', function($q) use ($transaction) {
                        $q->select('id')->from('orders')->where('order_code', $transaction->reference_code)->limit(1);
                    })->update(['status' => 'paid', 'paid_at' => now()]);
                } else {
                    return back()->with('error', 'Khách hàng không còn đủ tiền trong ví để thực hiện thanh toán này!');
                }
            }
            elseif ($oldStatus == 'success' && ($newStatus == 'pending' || $newStatus == 'failed')) {
                DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
                
                DB::table('payments')->where('order_id', function($q) use ($transaction) {
                    $q->select('id')->from('orders')->where('order_code', $transaction->reference_code)->limit(1);
                })->update(['status' => 'pending', 'paid_at' => null]);
            }
        }

        DB::table('wallet_transactions')->where('id', $id)->update([
            'status' => $newStatus
        ]);

        return back()->with('success', 'Hệ thống đã cập nhật trạng thái và điều chỉnh số dư ví!');
    }

    // ==========================================
    // 4. QUẢN LÝ HOÀN HÀNG / BẢO HÀNH
    // ==========================================
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

        if (!$refund) {
            return back()->with('error', 'Không tìm thấy yêu cầu hoàn trả này!');
        }

        if ($refund->status != 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó, không thể thay đổi lại!');
        }

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

                DB::table('orders')->where('id', $refund->order_id)->update([
                    'status' => 'refunded'
                ]);
            }
        }

        DB::table('refunds')->where('id', $id)->update([
            'status' => $newStatus
        ]);

        return back()->with('success', 'Đã xử lý yêu cầu hoàn trả và cập nhật trạng thái chiến dịch!');
    }
}