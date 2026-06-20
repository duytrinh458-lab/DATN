<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Product; // 💡 Thêm Model Product để quản lý kho

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
            return back()->with('success', 'Yêu cầu nạp tiền đã được gửi. Vui lòng chờ hệ thống xác nhận.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Không thể tạo yêu cầu nạp tiền.');
        }
    }

    /**
     * RÚT TIỀN (Chống lỗi âm ví bằng lockForUpdate)
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

        DB::beginTransaction();
        try {
            // Khóa dòng Ví để xếp hàng các request rút tiền trùng lặp
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                DB::rollBack();
                return back()->with('error', 'Không tìm thấy ví V-Pay.');
            }

            if ($wallet->balance < $request->amount) {
                DB::rollBack();
                return back()->with('error', 'Số dư không đủ để thực hiện giao dịch.');
            }

            $wallet->balance -= $request->amount;
            $wallet->save();

            Transaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'withdraw',
                'amount'         => $request->amount,
                'status'         => 'pending',
                'reference_code' => $request->bank_info 
            ]);

            DB::commit();
            return back()->with('success', 'Yêu cầu rút tiền đã được gửi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Không thể xử lý yêu cầu rút tiền.');
        }
    }

    /**
     * THANH TOÁN ĐẶT HÀNG (Vá lỗi Race Condition: Khóa kép cả Ví và Kho sản phẩm)
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        // 💡 Bắt đầu Transaction để bọc toàn bộ chu kỳ mua hàng
        DB::beginTransaction();
        try {
            // 🔒 KHÓA 1: Khóa tài khoản ví của User mua hàng
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            // 🔒 KHÓA 2: Khóa dòng thông tin của Sản phẩm đang được mua trong Kho
            $product = Product::where('id', $request->product_id)
                ->lockForUpdate()
                ->first();

            if (!$wallet || !$product) {
                throw new \Exception('Thông tin ví hoặc sản phẩm không hợp lệ.');
            }

            // Tính tổng tiền cần thanh toán
            $totalPrice = $product->price * $request->quantity;

            // 🔍 Kiểm tra điều kiện khi các luồng khác đã bị chặn bên ngoài cửa
            if ($wallet->balance < $totalPrice) {
                throw new \Exception('Số dư ví không đủ để thanh toán đơn hàng.');
            }

            if ($product->quantity < $request->quantity) {
                throw new \Exception('Sản phẩm trong kho đã hết hoặc không đủ số lượng.');
            }

            // 🛠️ Thực hiện trừ tiền và trừ kho thực tế một cách an toàn
            $wallet->balance -= $totalPrice;
            $wallet->save();

            $product->quantity -= $request->quantity;
            $product->save();

            // Ghi nhận lịch sử giao dịch mua hàng thành công
            Transaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'payment',
                'amount'         => $totalPrice,
                'status'         => 'success',
                'reference_code' => 'Mua ' . $request->quantity . ' x ' . $product->name
            ]);

            // Giải phóng khóa, hoàn tất giao dịch
            DB::commit();
            return back()->with('success', 'Đặt hàng và thanh toán thành công!');

        } catch (\Exception $e) {
            // Rollback toàn bộ dữ liệu kho và ví về trạng thái cũ nếu xảy ra lỗi
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}