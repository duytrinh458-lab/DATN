<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Address;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ hệ thống API liên quan đến Đơn hàng (Orders).
 * Bao gồm chu trình: Xem danh sách, Chi tiết đơn, Tạo đơn (thanh toán bằng ví), Hủy đơn, Theo dõi trạng thái/timeline,
 * Xác nhận nhận hàng, Yêu cầu hoàn tiền và các tác vụ quản trị của Admin (Duyệt hoàn tiền, Cập nhật trạng thái).
 */

/**
 * CHỨC NĂNG CLASS:
 * Điều phối dữ liệu phức tạp giữa nhiều bảng (orders, order_items, wallets, wallet_transactions, products, refunds).
 * Sử dụng Database Transaction quản lý nghiêm ngặt quy trình thanh toán nhằm đảm bảo tính toàn vẹn và chống gian lận.
 */
class OrderApiController extends Controller
{
    // =========================================================================
    // 📌 1. DANH SÁCH ĐƠN HÀNG (INDEX)
    // =========================================================================
    // VAI TRÒ: Lấy danh sách lịch sử mua hàng của người dùng đang đăng nhập.
    public function index()
    {
        // TRUY VẤN TỐI ƯU: Sử dụng Eager Loading (`with`) để nạp trước thông tin chi tiết item, sản phẩm bên trong và địa chỉ giao hàng.
        // Phân trang bằng `paginate(10)` để tối ưu hiệu năng đường truyền khi người dùng có quá nhiều đơn hàng.
        $orders = Order::with([
                'orderItems.product',
                'address'
            ])
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data'   => $orders
        ]);
    }

    // =========================================================================
    // 📌 2. CHI TIẾT ĐƠN HÀNG (SHOW)
    // =========================================================================
    // VAI TRÒ: Hiển thị đầy đủ thông tin của một đơn hàng cụ thể theo ID.
    public function show($id)
    {
        // RÀNG BUỘC BẢO MẬT: Phải check điều kiện `user_id` trùng với người đang đăng nhập để tránh xem lén đơn hàng của người khác.
        $order = Order::with([
                'orderItems.product',
                'address'
            ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $order
        ]);
    }

    // =========================================================================
    // 📌 3. TẠO ĐƠN HÀNG & THANH TOÁN (STORE)
    // =========================================================================
    // VAI TRÒ: Chuyển đổi từ giỏ hàng thành đơn hàng chính thức, trừ tiền ví điện tử và trừ kho sản phẩm.
    public function store(Request $request)
    {
        $userId = Auth::id();

        // KHỐI LỆNH KIỂM TRA: Xác thực giỏ hàng tồn tại và phải có ít nhất 1 sản phẩm mới cho phép đặt hàng.
        $cart = Cart::where('user_id', $userId)->first();

        if (
            !$cart ||
            DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->count() == 0
        ) {
            return response()->json([
                'status'  => false,
                'message' => 'Giỏ hàng của bạn đang trống!'
            ], 400);
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        // SỬ DỤNG TRY-CATCH VÀ TRANSACTION: Đảm bảo nếu một bước bị lỗi (như thiếu hàng, thiếu tiền), toàn bộ quá trình sẽ được hoàn tác.
        try {

            DB::beginTransaction();

            // -----------------------------------------------------------------
            // CHECK ADDRESS: Xác minh địa chỉ nhận hàng có phải của user này hay không
            // -----------------------------------------------------------------
            $address = Address::where('id', $request->address_id)
                ->where('user_id', $userId)
                ->first();

            if (!$address) {
                throw new \Exception('Địa chỉ giao hàng không hợp lệ.');
            }

            // -----------------------------------------------------------------
            // SHIPPING FEE: Tính toán phí vận chuyển tự động theo khu vực địa lý
            // -----------------------------------------------------------------
            $province = mb_strtolower($address->province, 'UTF-8');

            // Logic: Nội thành Hà Nội hoặc TP.HCM phí 30k, các tỉnh thành khác đồng giá 50k
            $shippingFee = (
                str_contains($province, 'hà nội') ||
                str_contains($province, 'hồ chí minh')
            ) ? 30000 : 50000;

            // -----------------------------------------------------------------
            // CART ITEMS: Tính tổng tiền hàng (Subtotal)
            // -----------------------------------------------------------------
            $cartItems = DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->get();

            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item->unit_price * $item->quantity;
            }

            // -----------------------------------------------------------------
            // WALLET CHECK: Kiểm tra và khóa số dư tài khoản
            // -----------------------------------------------------------------
            $totalAmountToPay = $subtotal + $shippingFee;

            // KỸ THUẬT lockForUpdate(): Khóa dòng dữ liệu ví của user lại để chống lỗi Race Condition (Ví dụ: User bấm thanh toán liên tục nhiều lần cùng 1 lúc).
            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$wallet || $wallet->balance < $totalAmountToPay) {
                throw new \Exception('Số dư ví V-Pay không đủ!');
            }

            // Thực hiện trừ tiền trực tiếp trên ví của user
            $wallet->balance -= $totalAmountToPay;
            $wallet->save();

            // -----------------------------------------------------------------
            // CREATE ORDER: Khởi tạo thông tin đơn hàng tổng quát
            // -----------------------------------------------------------------
            $order = Order::create([
                'user_id'      => $userId,
                'address_id'   => $request->address_id,
                'order_code'   => 'VG-' . strtoupper(Str::random(8)), // Tạo mã đơn hàng ngẫu nhiên duy nhất (Ví dụ: VG-A8FD3E9B)
                'subtotal'     => $subtotal,
                'shipping_fee' => $shippingFee,
                'total'        => $totalAmountToPay,
                'status'       => 'pending' // Trạng thái mặc định: Chờ xử lý
            ]);

            // -----------------------------------------------------------------
            // LOAD PRODUCTS: Nạp dữ liệu sản phẩm để check tồn kho
            // -----------------------------------------------------------------
            $productIds = $cartItems->pluck('product_id');
            // Tối ưu: Lấy danh sách sản phẩm bằng `whereIn` và gán Key bằng ID để tra cứu nhanh trong vòng lặp.
            $products = Product::whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            // -----------------------------------------------------------------
            // CREATE ORDER ITEMS: Đổ chi tiết sản phẩm vào đơn hàng và trừ kho
            // -----------------------------------------------------------------
            foreach ($cartItems as $item) {
                $product = $products[$item->product_id] ?? null;

                // Kiểm tra hàng trong kho xem còn đủ cung ứng số lượng mua không
                if (!$product || $product->stock < $item->quantity) {
                    throw new \Exception(
                        'Sản phẩm ' . ($product ? $product->name : '') . ' không đủ hàng!'
                    );
                }

                // Sao chép chi tiết item sang bảng 'order_items'
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item->product_id,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'total_price' => $item->unit_price * $item->quantity
                ]);

                // Trừ số lượng tồn kho của sản phẩm tương ứng
                $product->decrement('stock', $item->quantity);
            }

            // -----------------------------------------------------------------
            // WALLET TRANSACTION: Ghi nhận lịch sử biến động số dư tài khoản
            // -----------------------------------------------------------------
            DB::table('wallet_transactions')->insert([
                'wallet_id'      => $wallet->id,
                'type'           => 'payment', // Loại giao dịch: Thanh toán đơn hàng
                'amount'         => $totalAmountToPay,
                'reference_code' => $order->order_code,
                'status'         => 'success',
                'created_at'     => now()
            ]);

            // -----------------------------------------------------------------
            // CLEAR CART: Dọn sạch các mặt hàng cũ trong giỏ sau khi mua thành công
            // -----------------------------------------------------------------
            DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->delete();

            // Hoàn tất giao dịch an toàn
            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Đặt hàng thành công!',
                'data'    => $order
            ], 201);

        } catch (\Exception $e) {
            // Nếu có bất cứ trục trặc nào, hoàn tác lại toàn bộ tiền ví và số lượng tồn kho ban đầu
            DB::rollBack();

            $response = [
                'status'  => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng!'
            ];

            // Nếu đang bật chế độ debug, trả về nội dung lỗi chi tiết để lập trình viên xử lý
            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 400);
        }
    }

    // =========================================================================
    // 📌 4. HỦY ĐƠN HÀNG (CANCEL)
    // =========================================================================
    // VAI TRÒ: Người mua chủ động yêu cầu hủy đơn hàng và hệ thống tự động hoàn lại tiền vào ví điện tử.
    public function cancel($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        // LUỒNG NGHIỆP VỤ: Đơn hàng đã giao, đang giao hoặc đã hủy từ trước thì KHÔNG được phép hủy nữa.
        if (in_array($order->status, ['cancelled', 'delivered', 'shipping'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Đơn hàng này không thể hủy!'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Tiến hành khóa và nạp tiền hoàn trả vào ví của người mua
            $wallet = Wallet::where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($wallet) {
                $wallet->balance += $order->total;
                $wallet->save();

                // Tạo giao dịch lịch sử mang tính chất hoàn tiền (refund)
                DB::table('wallet_transactions')->insert([
                    'wallet_id'      => $wallet->id,
                    'type'           => 'refund',
                    'amount'         => $order->total,
                    'reference_code' => 'REF-' . $order->id . '-' . time(),
                    'status'         => 'success',
                    'created_at'     => now()
                ]);
            }

            // Cập nhật trạng thái đơn hàng thành đã hủy
            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Hủy đơn hàng thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'status'  => false,
                'message' => 'Có lỗi xảy ra!'
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        }
    }

    // =========================================================================
    // 📌 5. LẤY TRẠNG THÁI ĐƠN HÀNG (GET STATUS)
    // =========================================================================
    // VAI TRÒ: Kiểm tra nhanh trạng thái hiện tại của đơn hàng (dành cho cả User lẫn Admin).
    public function getStatus($id)
    {
        $user = Auth::user();

        // Sử dụng `select` để chỉ lấy ra các trường cần thiết, giảm tải dung lượng RAM xử lý dữ liệu.
        $query = Order::select('id', 'user_id', 'status', 'order_code')
            ->where('id', $id);

        // PHÂN QUYỀN: Nếu không phải Admin, ép buộc chỉ được xem trạng thái đơn hàng của chính bản thân mình.
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'order_code' => $order->order_code,
                'status'     => $order->status
            ]
        ]);
    }

    // =========================================================================
    // 📌 6. TIMELINE ĐƠN HÀNG (GET TIMELINE)
    // =========================================================================
    // VAI TRÒ: Trả về dòng thời gian lịch sử thay đổi để hiển thị giao diện Tracking (vẽ trục thời gian vận chuyển).
    public function getTimeline($id)
    {
        $user = Auth::user();
        $query = Order::where('id', $id);

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        // Khởi tạo mảng cấu trúc dòng thời gian mẫu dựa trên dấu mốc tạo đơn và cập nhật đơn gần nhất
        $timeline = [
            [
                'time' => $order->created_at,
                'desc' => 'Đơn hàng đã được tạo'
            ],
            [
                'time' => $order->updated_at,
                'desc' => 'Trạng thái hiện tại: ' . $order->status
            ]
        ];

        return response()->json([
            'status' => true,
            'data'   => $timeline
        ]);
    }

    // =========================================================================
    // 📌 7. XÁC NHẬN ĐÃ NHẬN HÀNG (CONFIRM RECEIVED)
    // =========================================================================
    // VAI TRÒ: Khách hàng bấm xác nhận khi shipper giao đồ đến, hoàn tất chu kỳ mua bán đơn hàng.
    public function confirmReceived($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        // ĐIỀU KIỆN LOGIC: Đơn hàng bắt buộc đang ở trạng thái 'shipping' (đang giao) thì mới cho phép bấm nhận hàng thành công.
        if ($order->status !== 'shipping') {
            return response()->json([
                'status'  => false,
                'message' => 'Đơn hàng chưa được giao!'
            ], 400);
        }

        // Chuyển dịch sang trạng thái 'delivered' (đã giao thành công)
        $order->status = 'delivered';
        $order->save();

        return response()->json([
            'status'  => true,
            'message' => 'Xác nhận nhận hàng thành công!'
        ]);
    }

    // =========================================================================
    // 📌 8. YÊU CẦU HOÀN TIỀN (REQUEST REFUND)
    // =========================================================================
    // VAI TRÒ: Khách hàng gửi yêu cầu cứu nại đòi hoàn tiền nếu hàng lỗi hoặc giao sai.
    public function requestRefund(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500' // Lý do bắt buộc nhập, tối đa 500 ký tự
        ]);

        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        // LUỒNG LOGIC: Chỉ chấp nhận hoàn tiền với các đơn hàng đang vận chuyển hoặc đã giao thành công.
        if (!in_array($order->status, ['shipping', 'delivered'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Đơn hàng chưa hợp lệ để hoàn tiền!'
            ], 400);
        }

        // CHẶN SPAM: Kiểm tra đơn hàng này đã từng nộp đơn yêu cầu hoàn tiền trước đó chưa.
        $alreadyRequested = Refund::where('order_id', $order->id)->exists();

        if ($alreadyRequested) {
            return response()->json([
                'status'  => false,
                'message' => 'Yêu cầu hoàn tiền đã tồn tại!'
            ], 400);
        }

        // Tạo bản ghi khiếu nại hoàn tiền ở trạng thái chờ duyệt (pending) để Admin xử lý sau
        Refund::create([
            'order_id'      => $order->id,
            'user_id'       => Auth::id(),
            'refund_amount' => $order->total,
            'reason'        => $request->reason,
            'status'        => 'pending'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Đã gửi yêu cầu hoàn tiền!'
        ]);
    }

    // =========================================================================
    // 📌 9. ADMIN: CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG (ADMIN UPDATE STATUS)
    // =========================================================================
    // VAI TRÒ: Quản trị viên thay đổi thủ công tiến trình của đơn hàng tại trang quản trị backend.
    public function adminUpdateStatus(Request $request, $id)
    {
        // RÀNG BUỘC CHẶN LỖI: Trạng thái nhập vào bắt buộc phải nằm trong danh sách các enum cho phép của hệ thống.
        $request->validate([
            'status' => 'required|in:pending,processing,shipping,delivered,cancelled'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không thấy đơn hàng'
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status'  => true,
            'message' => 'Đã cập nhật trạng thái đơn hàng'
        ]);
    }

    // =========================================================================
    // 📌 10. ADMIN: PHÊ DUYỆT HOÀN TIỀN (ADMIN APPROVE REFUND)
    // =========================================================================
    // VAI TRÒ: Admin đồng ý duyệt đơn khiếu nại của user, thực thi cộng lại tiền vào ví khách hàng và hủy đơn hàng.
    public function adminApproveRefund($refund_id)
    {
        try {
            DB::beginTransaction();

            // Khóa dòng dữ liệu khiếu nại để tránh tình trạng trùng lặp xử lý (Double Spend)
            $refund = Refund::where('id', $refund_id)
                ->lockForUpdate()
                ->first();

            // Chỉ xử lý các yêu cầu đang nằm ở trạng thái chờ duyệt (pending)
            if (!$refund || $refund->status != 'pending') {
                throw new \Exception('Yêu cầu không hợp lệ');
            }

            // Định vị ví tài khoản của người nhận tiền hoàn trả
            $wallet = Wallet::where('user_id', $refund->user_id)->first();

            if ($wallet) {
                // Cộng tiền hoàn trả vào tài khoản ví của user
                $wallet->balance += $refund->refund_amount;
                $wallet->save();

                // Lưu vết lịch sử giao dịch tăng tiền dạng refund
                DB::table('wallet_transactions')->insert([
                    'wallet_id'      => $wallet->id,
                    'type'           => 'refund',
                    'amount'         => $refund->refund_amount,
                    'reference_code' => 'REF-' . $refund->order_id,
                    'status'         => 'success',
                    'created_at'     => now()
                ]);
            }

            // Đổi trạng thái khiếu nại thành đã chấp thuận (approved)
            $refund->status = 'approved';
            $refund->refunded_at = now();
            $refund->save();

            // Tự động ép buộc cập nhật trạng thái đơn hàng gốc sang trạng thái đã hủy bỏ (cancelled)
            Order::where('id', $refund->order_id)->update([
                'status' => 'cancelled'
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Đã duyệt hoàn tiền!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'status'  => false,
                'message' => 'Có lỗi xảy ra!'
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 400);
        }
    }

    // =========================================================================
    // 📌 11. SỬA GHI CHÚ ĐƠN HÀNG (EDIT NOTE)
    // =========================================================================
    // VAI TRÒ: Người mua chỉnh sửa lời nhắn dặn dò shipper hoặc hệ thống trước khi hàng được bốc xếp đi giao.
    public function editNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:500'
        ]);

        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        // RÀNG BUỘC NGHIỆP VỤ: Chỉ cho phép chỉnh sửa lời nhắn khi đơn hàng ở trạng thái 'pending' (chờ duyệt) hoặc 'processing' (đang chuẩn bị hàng).
        // Khi hàng đã xếp lên xe phân phối đi giao (`shipping`, `delivered`, `cancelled`) thì KHÔNG được phép sửa nữa.
        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Không thể sửa ghi chú!'
            ], 400);
        }

        $order->note = $request->note;
        $order->save();

        return response()->json([
            'status'  => true,
            'message' => 'Đã cập nhật ghi chú!',
            'data' => [
                'order_id' => $order->id,
                'note'     => $order->note
            ]
        ]);
    }
}