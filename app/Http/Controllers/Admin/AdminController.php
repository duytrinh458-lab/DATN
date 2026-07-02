<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller chịu trách nhiệm điều hướng và xử lý toàn bộ các tác vụ quản trị (Admin).
 * Bao gồm: Xem thống kê tổng quan (Dashboard), cấu hình cổng thanh toán QR, quản lý lịch sử 
 * giao dịch ví điện tử, phê duyệt yêu cầu hoàn tiền và cung cấp dữ liệu số lượng thông báo (badge).
 */
class AdminController extends Controller
{
    // ==========================================
    // 1. TRANG TỔNG QUAN (DASHBOARD)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: dashboard()
     * Hàm này xử lý việc thu thập toàn bộ dữ liệu thống kê của hệ thống (sản phẩm, đơn hàng, 
     * doanh thu, người dùng,...) để hiển thị lên trang chủ giao diện Admin (Dashboard).
     */
    public function dashboard()
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI XỬ LÝ LƯU TRỮ TẠM THỜI (CACHE)
        |--------------------------------------------------------------------------
        | Ý nghĩa: Hàm Cache::remember sẽ kiểm tra xem trong bộ nhớ đệm có dữ liệu mang khóa 
        | 'admin_dashboard_stats' chưa. 
        | - Nếu CÓ: Lấy ra xài luôn, không cần truy vấn Database giúp trang tải cực nhanh.
        | - Nếu KHÔNG CÓ hoặc ĐÃ QUÁ 30 GIÂY: Hàm nặc danh (closure) bên trong sẽ chạy để 
        |   truy vấn lại Database, sau đó lưu lại vào Cache trong 30 giây cho các lần tải sau.
        */
        $stats = Cache::remember('admin_dashboard_stats', 30, function () {

            return [
                /*
                |--------------------------------------------------------------------------
                | THỐNG KÊ SỐ LƯỢNG (Đếm tổng số dòng thỏa mãn điều kiện)
                |--------------------------------------------------------------------------
                */
                // Đếm tất cả sản phẩm đang ở trạng thái hoạt động ('active') để hiển thị trên web
                'productCount' => Product::where('status', 'active')->count(),

                // Đếm tổng số đơn hàng đã được tạo từ trước đến nay (không phân biệt trạng thái)
                'orderCount' => Order::count(),

                // Đếm số tài khoản thuộc vai trò là khách hàng ('customer'), loại bỏ tài khoản Admin
                'userCount' => User::where('role', 'customer')->count(),

                // Đếm tổng số lượt đánh giá/bình luận của khách hàng trong bảng 'reviews' bằng Query Builder
                'commentCount' => DB::table('reviews')->count(),

                /*
                |--------------------------------------------------------------------------
                | ĐƠN HÀNG CHỜ XỬ LÝ
                |--------------------------------------------------------------------------
                */
                // Đếm các đơn hàng mới đang ở trạng thái chờ Admin duyệt ('pending')
                'pendingOrders' => Order::where('status', 'pending')->count(),

                /*
                |--------------------------------------------------------------------------
                | TỔNG DOANH THU
                |--------------------------------------------------------------------------
                */
                // Tính tổng tiền (hàm sum) của cột 'total' từ các đơn hàng đã giao thành công ('delivered')
                'revenue' => Order::where('status', 'delivered')->sum('total'),

                /*
                |--------------------------------------------------------------------------
                | SẢN PHẨM BÁN CHẠY NHẤT (Truy vấn SQL phức tạp)
                |--------------------------------------------------------------------------
                | Ý tưởng thuật toán:
                | 1. Kết nối (JOIN) bảng chi tiết mặt hàng đơn ('order_items') với bảng sản phẩm ('products') dựa trên ID sản phẩm.
                | 2. Nhóm (GROUP BY) theo ID và tên sản phẩm.
                | 3. Tính tổng số lượng bán ra (SUM) của từng sản phẩm và đặt tên cột ảo là 'total_sold'.
                | 4. Sắp xếp giảm dần (orderByDesc) theo lượng bán ra và dùng 'first()' để lấy duy nhất 1 sản phẩm cao nhất.
                */
                'bestProduct' => DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->select(
                        'products.name',
                        DB::raw('SUM(order_items.quantity) as total_sold')
                    )
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('total_sold')
                    ->first()
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | TRẢ VỀ GIAO DIỆN (RETURN VIEW)
        |--------------------------------------------------------------------------
        | Truyền mảng dữ liệu vừa lấy từ bộ nhớ Cache ($stats) sang file giao diện 
        | blade 'admin.dashboard' để hiển thị lên màn hình cho Admin xem.
        */
        return view('admin.dashboard', [
            'productCount'  => $stats['productCount'],
            'orderCount'    => $stats['orderCount'],
            'userCount'     => $stats['userCount'],
            'revenue'       => $stats['revenue'],
            'bestProduct'   => $stats['bestProduct'],
            'commentCount'  => $stats['commentCount'],
            'pendingOrders' => $stats['pendingOrders']
        ]);
    }

    // ==========================================
    // 2. QUẢN LÝ CẤU HÌNH QR V-PAY
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: showQRSettings()
     * Hiển thị giao diện cấu hình ảnh mã QR thanh toán ngân hàng của hệ thống.
     */
    public function showQRSettings()
    {
        /*
        |--------------------------------------------------------------------------
        | LẤY SỐ LƯỢNG ĐƠN HÀNG CHỜ XỬ LÝ ĐỂ LÀM BADGE THÔNG BÁO
        |--------------------------------------------------------------------------
        | Biến $pendingOrders lưu số lượng đơn hàng 'pending' để hiển thị chấm đỏ thông báo 
        | trên thanh menu điều hướng bên cạnh mục "Quản lý đơn hàng".
        */
        $pendingOrders = Order::where(
            'status',
            'pending'
        )->count();

        // Trả về view cài đặt QR kèm theo biến số lượng đơn hàng chờ xử lý
        return view(
            'Admin.settings.qr',
            compact('pendingOrders')
        );
    }

    /**
     * VAI TRÒ CỦA METHOD: updateQR()
     * Xử lý hành động gửi ảnh (upload file) mã QR mới từ form cài đặt lên máy chủ.
     * Biến quan trọng: $request (chứa toàn bộ thông tin và file từ form gửi lên).
     */
    public function updateQR(Request $request)
    {
        // Kiểm tra tính hợp lệ (Validate): Bắt buộc phải chọn file ('required'), 
        // file phải là ảnh ('image'), định dạng cho phép và dung lượng tối đa 2MB (2048 KB).
        $request->validate([
            'qr_code' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // KHỐI LỆNH KIỂM TRA VÀ XỬ LÝ FILE: Nếu người dùng thực sự có đính kèm file hợp lệ
        if ($request->hasFile('qr_code')) {
            // Lấy đối tượng file ảnh ra từ request
            $image = $request->file('qr_code');

            // Đặt tên file cố định thành 'qr-demo.png' để đè lên file cũ, tránh rác bộ nhớ
            $fileName = 'qr-demo.png';

            // Di chuyển (upload) file ảnh vào thư mục 'public/images' trên hosting/máy chủ
            $image->move(
                public_path('images'),
                $fileName
            );

            // Chuyển hướng quay lại trang cũ kèm thông báo xanh (success) thông báo thành công
            return redirect()
                ->back()
                ->with(
                    'success',
                    'Đã cập nhật mã QR ngân hàng mới thành công!'
                );
        }

        // Trường hợp lỗi kỹ thuật không tìm thấy file, quay lại kèm thông báo đỏ (error)
        return redirect()
            ->back()
            ->with(
                'error',
                'Có lỗi xảy ra khi tải ảnh lên.'
            );
    }

    // ==========================================
    // 3. QUẢN LÝ GIAO DỊCH VÍ V-PAY
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: transactions()
     * Lấy danh sách lịch sử nạp tiền, rút tiền, thanh toán qua ví điện tử của toàn bộ 
     * người dùng hệ thống để hiển thị cho Admin quản lý.
     */
    public function transactions()
    {
        /*
        |--------------------------------------------------------------------------
        | TRUY VẤN LẤY LỊCH SỬ GIAO DỊCH VÀ THÔNG TIN NGƯỜI DÙNG
        |--------------------------------------------------------------------------
        | Biến $transactions: Lưu danh sách dữ liệu sau khi kết nối (JOIN) 3 bảng:
        | - 'wallet_transactions' (Bảng gốc chứa thông tin số tiền, loại giao dịch)
        | - 'wallets' (Kết nối qua wallet_id để biết giao dịch này thuộc ví nào)
        | - 'users' (Kết nối tiếp qua user_id để lấy họ tên, email, ảnh đại diện của chủ ví)
        | Sắp xếp theo ngày tạo giảm dần (created_at desc) để giao dịch mới nhất hiện lên đầu.
        | Phân trang (paginate): Chỉ lấy đúng 5 dòng dữ liệu cho 1 trang để tránh quá tải giao diện.
        */
        $transactions = DB::table('wallet_transactions')
            ->join(
                'wallets',
                'wallet_transactions.wallet_id',
                '=',
                'wallets.id'
            )
            ->join(
                'users',
                'wallets.user_id',
                '=',
                'users.id'
            )
            ->select(
                'wallet_transactions.*',
                'users.full_name as user_name',
                'users.avatar as user_avatar',
                'users.email'
            )
            ->orderByDesc(
                'wallet_transactions.created_at'
            )
            ->paginate(5);

        // Lấy số lượng đơn hàng chờ xử lý để hiển thị chấm đỏ (badge) thông báo trên menu sidebar
        $pendingOrders = Order::where(
            'status',
            'pending'
        )->count();

        // Trả về view danh sách giao dịch kèm theo các dữ liệu vừa tính toán được ở trên
        return view(
            'Admin.transactions.index',
            compact(
                'transactions',
                'pendingOrders'
            )
        );
    }

    /**
     * VAI TRÒ CỦA METHOD: updateTransactionStatus()
     * Hàm cực kỳ quan trọng dùng để Phê duyệt hoặc Từ chối một giao dịch ví (Nạp/Rút/Thanh toán).
     * Khi trạng thái giao dịch thay đổi, số dư tiền trong ví của khách hàng phải thay đổi tương ứng.
     * Biến quan trọng: $id (ID của giao dịch cần sửa), $request->status (Trạng thái mới do admin chọn).
     */
    public function updateTransactionStatus(Request $request, $id) 
    {
        $newStatus = $request->status;

        // Tìm thông tin giao dịch hiện tại trong Database dựa vào ID nhận được
        $transaction = DB::table('wallet_transactions')
            ->where('id', $id)
            ->first();

        // KHỐI LỆNH KIỂM TRA AN TOÀN (VALIDATION):
        // 1. Nếu không tìm thấy giao dịch nào trùng ID, thông báo lỗi ngay lập tức
        if (!$transaction) {
            return back()->with(
                'error',
                'Giao dịch không tồn tại!'
            );
        }

        $oldStatus = $transaction->status;

        // 2. Chặn tuyệt đối không cho sửa nếu giao dịch này vốn dĩ ĐÃ THÀNH CÔNG ('success') từ trước
        // Điều này ngăn chặn việc Admin bấm nhầm làm thay đổi số dư tiền nhiều lần gây thất thoát.
        if ($oldStatus == 'success') {
            return back()->with(
                'error',
                'Giao dịch đã thành công, không thể thay đổi.'
            );
        }

        // 3. Nếu Admin bấm lưu nhưng không hề đổi trạng thái khác đi thì không cần xử lý gì thêm
        if ($oldStatus == $newStatus) {
            return back()->with(
                'success',
                'Trạng thái không thay đổi.'
            );
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | SỬ DỤNG DATABASE TRANSACTION (ĐẢM BẢO AN TOÀN TIỀN TỆ)
            |--------------------------------------------------------------------------
            | Ý tưởng: Lệnh `beginTransaction()` giúp bao bọc toàn bộ các lệnh SQL sửa đổi bên dưới.
            | Nếu tất cả chạy mượt mà, lệnh `commit()` ở cuối sẽ lưu vĩnh viễn vào DB.
            | Nếu có bất kỳ dòng nào bị lỗi hệ thống, lệnh `rollBack()` ở khối catch sẽ kích hoạt,
            | khôi phục toàn bộ dữ liệu về trạng thái ban đầu, cam kết không bị lỗi lệch tiền.
            */
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | TRƯỜNG HỢP 1: GIAO DỊCH NẠP TIỀN (DEPOSIT)
            |--------------------------------------------------------------------------
            */
            if ($transaction->type == 'deposit') {
                // Nếu trạng thái cũ là đang chờ ('pending') và Admin duyệt thành công ('success')
                // -> Tiến hành CỘNG thêm tiền vào ví của khách (increment).
                if (
                    $oldStatus == 'pending'
                    && $newStatus == 'success'
                ) {
                    DB::table('wallets')
                        ->where(
                            'id',
                            $transaction->wallet_id
                        )
                        ->increment(
                            'balance',
                            $transaction->amount
                        );
                }
                // Nếu giao dịch đang thành công nhưng bị chuyển ngược về chờ/thất bại (Logic gốc hệ thống)
                // -> Tiến hành TRỪ bớt tiền ra khỏi ví của khách (decrement).
                elseif (
                    $oldStatus == 'success'
                    && (
                        $newStatus == 'pending'
                        || $newStatus == 'failed'
                    )
                ) {
                    DB::table('wallets')
                        ->where(
                            'id',
                            $transaction->wallet_id
                        )
                        ->decrement(
                            'balance',
                            $transaction->amount
                        );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TRƯỜNG HỢP 2: GIAO DỊCH RÚT TIỀN (WITHDRAW)
            |--------------------------------------------------------------------------
            */
            elseif ($transaction->type == 'withdraw') {
                // Nếu trạng thái cũ là đang chờ duyệt nhưng Admin bấm Từ chối/Thất bại ('failed')
                // -> Trả lại (CỘNG lại) tiền vào ví cho khách vì họ rút không thành công.
                if (
                    $oldStatus == 'pending'
                    && $newStatus == 'failed'
                ) {
                    DB::table('wallets')
                        ->where(
                            'id',
                            $transaction->wallet_id
                        )
                        ->increment(
                            'balance',
                            $transaction->amount
                        );
                }
                // Nếu trạng thái cũ là thất bại nay chuyển về chờ duyệt (Logic gốc hệ thống)
                // -> TRỪ bớt tiền trong ví đi để tiếp tục chờ xử lý rút tiền.
                elseif (
                    $oldStatus == 'failed'
                    && $newStatus == 'pending'
                ) {
                    DB::table('wallets')
                        ->where(
                            'id',
                            $transaction->wallet_id
                        )
                        ->decrement(
                            'balance',
                            $transaction->amount
                        );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TRƯỜNG HỢP 3: GIAO DỊCH THANH TOÁN MUA HÀNG (PAYMENT)
            |--------------------------------------------------------------------------
            */
            // Phân nhánh lớn xử lý khi khách hàng dùng ví để thanh toán hóa đơn
            else if ($transaction->type == 'payment') {

                // Nhánh phụ: Từ chờ xử lý chuyển sang thành công (Duyệt thanh toán đơn hàng)
                if (
                    $oldStatus == 'pending'
                    && $newStatus == 'success'
                ) {
                    // Dùng lockForUpdate() để khóa dòng dữ liệu ví này lại tạm thời, tránh việc 
                    // khách hàng bấm thanh toán 2 dòng lệnh cùng 1 mili-giây gây lỗi nhân đôi số tiền (Race Condition).
                    $wallet = DB::table('wallets')
                        ->where('id', $transaction->wallet_id)
                        ->lockForUpdate()
                        ->first();

                    // Kiểm tra xem số dư ví hiện tại có đủ để thanh toán số tiền của giao dịch không
                    if (
                        $wallet &&
                        $wallet->balance >= $transaction->amount
                    ) {
                        // Nếu đủ tiền: Tiến hành TRỪ tiền trong ví của khách hàng
                        DB::table('wallets')
                            ->where('id', $transaction->wallet_id)
                            ->decrement(
                                'balance',
                                $transaction->amount
                            );

                    } else {
                        // Nếu tài khoản không đủ tiền: Ngay lập tức hủy bỏ tiến trình (rollBack) 
                        // và báo lỗi ra màn hình, không cho phép cập nhật trạng thái thành công.
                        DB::rollBack();

                        return back()->with(
                            'error',
                            'Khách hàng không còn đủ tiền trong ví!'
                        );
                    }
                }
                // Nhánh phụ: Nếu giao dịch thanh toán vốn dĩ đã thành công nay bị Admin chuyển thành chờ/thất bại 
                // (Ví dụ: Khách hủy đơn hàng, trả hàng) -> Hoàn lại (CỘNG lại) tiền vào ví cho khách.
                elseif (
                    $oldStatus == 'success'
                    &&
                    (
                        $newStatus == 'pending'
                        || $newStatus == 'failed'
                    )
                ) {
                    DB::table('wallets')
                        ->where('id', $transaction->wallet_id)
                        ->increment(
                            'balance',
                            $transaction->amount
                        );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CẬP NHẬT TRẠNG THÁI MỚI VÀO DATABASE
            |--------------------------------------------------------------------------
            | Sau khi đã xử lý cộng trừ tiền ví hợp lý ở các khối lệnh trên, tiến hành 
            | lưu trạng thái mới ('success', 'failed', 'pending') vào bảng lịch sử giao dịch.
            */
            DB::table('wallet_transactions')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus
                ]);

            // Chấp nhận và lưu mọi thay đổi dữ liệu vào Database vĩnh viễn
            DB::commit();

            return back()->with(
                'success',
                'Đã cập nhật trạng thái giao dịch!'
            );

        } catch (\Exception $e) {
            // KHỐI LỆNH XỬ LÝ LỖI: Nếu trong khối 'try' phát sinh bất kỳ lỗi code hay crash DB nào,
            // hệ thống nhảy vào đây để hủy bỏ toàn bộ tác vụ (rollBack) để bảo vệ số dư tài khoản khách.
            DB::rollBack();

            return back()->with(
                'error',
                'Đã xảy ra lỗi: ' . $e->getMessage()
            );
        }
    }

    // ==========================================
    // 4. QUẢN LÝ HOÀN HÀNG / BẢO HÀNH
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: refunds()
     * Hiển thị danh sách các yêu cầu đòi hoàn tiền/trả hàng từ phía người mua lên trang Admin.
     */
    public function refunds()
    {
        // Đếm tổng số lượng yêu cầu hoàn tiền đang ở trạng thái chờ duyệt ('pending')
        $pendingRefunds = DB::table('refunds')
            ->where('status', 'pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | LẤY THÔNG TIN HOÀN TIỀN PHÂN TRANG
        |--------------------------------------------------------------------------
        | Kết nối bảng 'refunds' với bảng 'users' (để lấy tên, ảnh đại diện người đòi hoàn tiền) 
        | và kết nối bảng 'orders' (để lấy mã đơn hàng gốc 'order_code' và tổng số tiền của đơn đó).
        | Sắp xếp yêu cầu hoàn tiền mới nhất lên đầu và phân trang 5 mục/trang.
        */
        $refunds = DB::table('refunds')
            ->join('users', 'refunds.user_id', '=', 'users.id')
            ->join('orders', 'refunds.order_id', '=', 'orders.id')
            ->select(
                'refunds.*',
                'users.full_name as user_name',
                'users.avatar as user_avatar',
                'orders.order_code',
                'orders.total'
            )
            ->orderByDesc('refunds.created_at')
            ->paginate(5);

        // Lấy số lượng đơn hàng mới chờ duyệt để làm số thông báo trên sidebar
        $pendingOrders = Order::where('status', 'pending')->count();

        // Trả về view quản lý hoàn tiền kèm theo 3 biến chứa dữ liệu cần thiết
        return view('admin.refunds.index', compact(
            'refunds',
            'pendingOrders',
            'pendingRefunds'
        ));
    }

    /**
     * VAI TRÒ CỦA METHOD: updateRefundStatus()
     * Xử lý phê duyệt hoặc từ chối yêu cầu hoàn tiền của khách hàng. 
     * Nếu Admin DUYỆT hoàn tiền, hệ thống sẽ tự động chuyển tiền từ hệ thống hoàn lại vào ví điện tử của khách.
     * Biến quan trọng: $id (ID của dòng yêu cầu hoàn tiền), $request->status (Trạng thái duyệt: 'approved' hoặc 'rejected').
     */
    public function updateRefundStatus(Request $request, $id) 
    {
        $newStatus = $request->status;

        // Tìm kiếm thông tin bản ghi hoàn tiền theo ID truyền vào
        $refund = DB::table('refunds')
            ->where('id', $id)
            ->first();

        // Nếu không tồn tại bản ghi hoàn tiền này, trả về thông báo lỗi
        if (!$refund) {
            return back()->with(
                'error',
                'Không tìm thấy yêu cầu hoàn trả này!'
            );
        }

        // Chặn không cho xử lý lại nếu trạng thái hiện tại khác 'pending' (nghĩa là đã được duyệt hoặc từ chối trước đó rồi)
        if ($refund->status != 'pending') {
            return back()->with(
                'error',
                'Yêu cầu này đã được xử lý trước đó!'
            );
        }

        try {
            // Khởi động tiến trình dữ liệu an toàn nhằm đồng bộ hóa việc cộng tiền ví và đổi trạng thái đơn hàng
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | KHỐI XỬ LÝ KHI ADMIN ĐỒNG Ý DUYỆT HOÀN TIỀN ('approved')
            |--------------------------------------------------------------------------
            */
            if ($newStatus == 'approved') {

                // 1. Lấy thông tin đơn hàng liên quan để biết chính xác số tiền cần hoàn lại ($order->total)
                $order = DB::table('orders')
                    ->where(
                        'id',
                        $refund->order_id
                    )
                    ->first();

                // 2. Lấy thông tin ví điện tử của khách hàng gửi yêu cầu hoàn tiền
                $wallet = DB::table('wallets')
                    ->where(
                        'user_id',
                        $refund->user_id
                    )
                    ->first();

                // Nếu tìm thấy đầy đủ cả ví lẫn đơn hàng của người dùng đó
                if ($wallet && $order) {

                    // HÀNH ĐỘNG A: Cộng tiền của đơn hàng bị hủy ngược trở lại vào số dư ví của người dùng
                    DB::table('wallets')
                        ->where(
                            'id',
                            $wallet->id
                        )
                        ->increment(
                            'balance',
                            $order->total
                        );

                    // HÀNH ĐỘNG B: Chèn thêm 1 dòng lịch sử vào bảng giao dịch ví ('wallet_transactions')
                    // Ghi nhận đây là giao dịch loại hoàn tiền ('type' => 'refund') ở trạng thái thành công ('success')
                    DB::table('wallet_transactions')
                        ->insert([
                            'wallet_id'  => $wallet->id,
                            'type'       => 'refund',
                            'amount'     => $order->total,
                            'status'     => 'success',
                            'created_at' => now()
                        ]);

                    // HÀNH ĐỘNG C: Cập nhật trạng thái của đơn hàng gốc chuyển thành Đã hoàn tiền ('refunded')
                    DB::table('orders')
                        ->where(
                            'id',
                            $refund->order_id
                        )
                        ->update([
                            'status' => 'refunded'
                        ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CẬP NHẬT TRẠNG THÁI CHO YÊU CẦU HOÀN TIỀN
            |--------------------------------------------------------------------------
            | Cập nhật trạng thái của chính bản ghi hoàn tiền này thành 'approved' (Đã duyệt) 
            | hoặc 'rejected' (Bị từ chối) tùy theo lựa chọn của Admin.
            */
            DB::table('refunds')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus
                ]);

            // Xác nhận hoàn tất lưu các thay đổi vào DB một cách an toàn
            DB::commit();

            return back()->with(
                'success',
                'Đã xử lý yêu cầu hoàn tiền'
            );

        } catch (\Exception $e) {
            // Nếu có lỗi bất ngờ xảy ra, khôi phục lại dữ liệu gốc ban đầu để tránh sai sót tài chính
            DB::rollBack();

            return back()->with(
                'error',
                'Lỗi hệ thống: ' . $e->getMessage()
            );
        }
    }

    /**
     * VAI TRÒ CỦA METHOD: badges()
     * Đây là một API endpoint, trả về dữ liệu số lượng các tác vụ chưa xử lý dưới dạng chuỗi JSON.
     * Mục đích: Giúp giao diện Admin gọi qua AJAX/Fetch để cập nhật số lượng thông báo (badge) 
     * theo thời gian thực mà không cần tải lại toàn bộ trang web.
     */
    public function badges()
    {
        try {
            // Trả về phản hồi dạng JSON chứa các cặp key-value đếm từ Database
            return response()->json([
                // Đếm số lượng đơn hàng đang chờ xử lý
                'pendingOrders'  => Order::where('status', 'pending')->count(),

                // Đếm số lượng yêu cầu hoàn hàng đang chờ xử lý
                'pendingRefunds' => DB::table('refunds')
                    ->where('status', 'pending')
                    ->count(),

                // Đếm tổng số lượng đánh giá/bình luận sản phẩm hiện có
                'reviews'        => DB::table('reviews')->count(),
            ]);

        } catch (\Exception $e) {
            // Nếu API bị lỗi, trả về chuỗi JSON thông báo lỗi kèm mã trạng thái lỗi hệ thống 500
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}