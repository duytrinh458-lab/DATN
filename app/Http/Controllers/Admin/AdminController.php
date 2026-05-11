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

        // Doanh thu từ các đơn hàng đã giao thành công
        $revenue = Order::where('status', 'delivered')->sum('total');

        // Lấy sản phẩm có lượt bán cao nhất
        $bestProduct = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();

        // Đếm tổng số lượt đánh giá từ bảng reviews
        $commentCount = DB::table('reviews')->count();

        return view('Admin.dashboard', compact(
            'productCount',
            'orderCount',
            'userCount',
            'revenue',
            'bestProduct',
            'commentCount'
        ));
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
            $fileName = 'qr-demo.png'; // Ghi đè file ảnh cũ để không tốn bộ nhớ Database
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
            // Sử dụng users.full_name vì DB của Duy không có cột name
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
            // Nạp tiền: Từ Chờ sang Thành công -> Cộng tiền vào ví
            if ($oldStatus == 'pending' && $newStatus == 'success') {
                DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
            } 
            // Nạp tiền: Đang Thành công bẻ lái sang Chờ/Thất bại -> Trừ lại tiền trong ví
            elseif ($oldStatus == 'success' && ($newStatus == 'pending' || $newStatus == 'failed')) {
                DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
            }
        } 
        elseif ($transaction->type == 'withdraw') {
            // Rút tiền: Nếu chuyển sang Thất bại -> Trả lại tiền vào ví cho khách
            if ($oldStatus == 'pending' && $newStatus == 'failed') {
                DB::table('wallets')->where('id', $transaction->wallet_id)->increment('balance', $transaction->amount);
            }
            // Rút tiền: Đang Thất bại chuyển lại Chờ duyệt -> Tạm trừ tiền đi
            elseif ($oldStatus == 'failed' && $newStatus == 'pending') {
                DB::table('wallets')->where('id', $transaction->wallet_id)->decrement('balance', $transaction->amount);
            }
        }

        // Cập nhật trạng thái mới. Đã xóa cột updated_at vì bảng wallet_transactions không có cột này.
        DB::table('wallet_transactions')->where('id', $id)->update([
            'status' => $newStatus
        ]);

        return back()->with('success', 'Hệ thống đã cập nhật trạng thái và điều chỉnh số dư ví!');
    }
}