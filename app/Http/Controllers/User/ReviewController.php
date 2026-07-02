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
    // =========================================================================
    // 1. GIAO DIỆN LỊCH SỬ GIAO DỊCH & SỐ DƯ (DASHBOARD)
    // =========================================================================
    /**
     * Chức năng: Hiển thị số dư ví V-Pay hiện tại và phân trang lịch sử biến động số dư.
     * Cơ chế phòng thủ: Tự động khởi tạo ví mới với số dư 0đ nếu tài khoản chưa từng có ví.
     */
    public function index()
    {
        $user = Auth::user();

        // Nguyên tắc an toàn: Dùng firstOrCreate để đảm bảo luôn có thực thể Ví khi truy cập trang này
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Lấy lịch sử nạp, rút, thanh toán xếp mới nhất lên đầu (Phân trang 5 dòng để UI nhỏ gọn)
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(5);

        return view('User.wallet.index', [
            'balance'      => $wallet->balance,
            'transactions' => $transactions
        ]);
    }

    // =========================================================================
    // 2. YÊU CẦU NẠP TIỀN VÀO VÍ (DEPOSIT)
    // =========================================================================
    /**
     * Chức năng: Tạo lệnh nạp tiền vào ví V-Pay.
     * Logic nghiệp vụ: Trạng thái ban đầu luôn là 'pending', chờ Admin duyệt chuyển khoản thực tế mới cộng tiền.
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
            // Chỉ tạo yêu cầu nạp tiền ở trạng thái chờ duyệt (Chưa cộng tiền trực tiếp vào trường balance)
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

    // =========================================================================
    // 3. YÊU CẦU RÚT TIỀN (WITHDRAW)
    // =========================================================================
    /**
     * Chức năng: Trừ tiền trực tiếp từ ví và tạo lệnh chờ Admin chuyển khoản về ngân hàng.
     * Cơ chế an toàn: Sử dụng Biện pháp khóa dòng Pessimistic Locking (lockForUpdate) để chống lỗi rút âm ví.
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
            // 🔒 LOCKFORUPDATE: Khóa dòng ví này lại. Nếu user bấm nút rút tiền liên tục (Spam Click), 
            // các Request sau phải xếp hàng đợi Request trước xử lý xong, ngăn chặn lỗi Race Condition tuyệt đối.
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                DB::rollBack();
                return back()->with('error', 'Không tìm thấy ví V-Pay.');
            }

            // Kiểm tra số dư ngay trong trạng thái dòng dữ liệu đang bị khóa cứng
            if ($wallet->balance < $request->amount) {
                DB::rollBack();
                return back()->with('error', 'Số dư không đủ để thực hiện giao dịch.');
            }

            // Trừ tiền ngay lập tức để giữ an toàn cho quỹ hệ thống
            $wallet->balance -= $request->amount;
            $wallet->save();

            // Lưu vết giao dịch để kế toán đối soát qua thông tin ngân hàng (reference_code)
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

    // =========================================================================
    // 4. THANH TOÁN MUA HÀNG TRỰC TIẾP (PURCHASE)
    // =========================================================================
    /**
     * Chức năng: Thực hiện trừ tiền ví và trừ số lượng sản phẩm trong kho cùng một lúc.
     * Kiến trúc nâng cao: Áp dụng cơ chế Khóa kép (Dual-Locking Matrix) loại bỏ lỗi tranh chấp kho/ví.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        // 💡 Bắt đầu Transaction để bọc toàn bộ chu kỳ mua hàng thành một khối nguyên tử (Atomicity)
        DB::beginTransaction();
        try {
            // 🔒 KHÓA 1: Khóa tài khoản ví của người mua hàng để chốt số dư cố định
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

            // Tính tổng tiền cần thanh toán dựa trên giá gốc gốc của sản phẩm ($product->price)
            $totalPrice = $product->price * $request->quantity;

            // Kiểm tra tài chính: Số dư ví có gánh nổi tổng hóa đơn đơn hàng không?
            if ($wallet->balance < $totalPrice) {
                throw new \Exception('Số dư ví không đủ để thanh toán đơn hàng.');
            }

            // Kiểm tra vật lý kho: Sản phẩm trong kho có đủ số lượng bàn giao không?
            if ($product->quantity < $request->quantity) {
                throw new \Exception('Sản phẩm trong kho đã hết hoặc không đủ số lượng.');
            }

            // Vận hành trừ song song: Đảm bảo được ăn cả, ngã về không
            $wallet->balance -= $totalPrice;
            $wallet->save();

            $product->quantity -= $request->quantity;
            $product->save();

            // Ghi nhận lịch sử giao dịch mua hàng thành công (status = success)
            Transaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'payment',
                'amount'         => $totalPrice,
                'status'         => 'success',
                'reference_code' => 'Mua ' . $request->quantity . ' x ' . $product->name
            ]);

            // Giải phóng tất cả các hàng đợi, hoàn tất lưu dữ liệu vĩnh viễn
            DB::commit();
            return back()->with('success', 'Đặt hàng và thanh toán thành công!');
        } catch (\Exception $e) {
            // Nếu có bất kỳ lỗi phát sinh nào ở giữa chu kỳ, phục hồi nguyên trạng cả kho lẫn ví như cũ
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}