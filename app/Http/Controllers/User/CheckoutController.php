<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. TÍNH NĂNG MUA NGAY (BUY NOW)
    |--------------------------------------------------------------------------
    | Chức năng: Bỏ qua bước vào giỏ hàng, đưa thẳng thiết bị ra trang thanh toán
    | giúp tối ưu tỷ lệ chuyển đổi đơn hàng cho hệ thống.
    */
    public function buyNow(Request $request)
    {
        // Tìm thông tin UAV khách muốn mua ngay kèm theo hình ảnh
        $product = Product::with('images')->findOrFail($request->product_id);

        // Đóng gói thông tin sản phẩm và lưu tạm vào Bộ nhớ đệm (Session) dưới dạng một mảng dữ liệu checkout
        session([
            'checkout_items' => [
                [
                    'is_buy_now' => true, // Đánh dấu đây là đơn hàng "Mua ngay" để phân biệt với đơn hàng từ giỏ
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'price'      => $product->sale_price,
                    'quantity'   => $request->quantity ?? 1,
                    'image'      => $product->images->first()->image_url ?? 'default.jpg'
                ]
            ]
        ]);

        // Chuyển hướng thẳng khách hàng đến trang điền thông tin giao hàng và thanh toán
        return redirect()->route('user.checkout.index');
    }

    /*
    |--------------------------------------------------------------------------
    | 2. TRANG HIỂN THỊ THÔNG TIN THANH TOÁN (CHECKOUT PAGE)
    |--------------------------------------------------------------------------
    | Chức năng: Tập hợp sản phẩm, tính tổng tiền, lấy danh sách địa chỉ nhận hàng
    | để chuẩn bị khởi tạo đơn hàng chính thức.
    */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Kiểm tra quyền truy cập: Bắt buộc người dùng phải đăng nhập hệ thống bảo mật
        if (!$userId) {
            return redirect()
                ->route('login')
                ->with('error', 'Đặc vụ cần đăng nhập để thực hiện nhiệm vụ.');
        }

        $checkoutItems = [];
        $total = 0;

        /*
        |--------------------------------------------------------------------------
        | LUỒNG A: ƯU TIÊN XỬ LÝ SẢN PHẨM ĐƯỢC CHỌN TỪ GIỎ HÀNG
        |--------------------------------------------------------------------------
        */
        if ($request->has('items') && is_array($request->items)) {

            // Lấy danh sách các sản phẩm được tick chọn trong giỏ hàng của chính user này
            $cartItems = CartItem::with('product.images')
                ->whereHas('cart', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->whereIn('id', $request->items)
                ->get();

            // Chuyển đổi dữ liệu từ giỏ hàng sang định dạng chuẩn của trang Thanh toán
            foreach ($cartItems as $cItem) {
                $checkoutItems[] = [
                    'is_cart'      => true, // Đánh dấu sản phẩm này đến từ giỏ hàng (để xóa khỏi giỏ sau khi đặt hàng thành công)
                    'cart_item_id' => $cItem->id,
                    'product_id'   => $cItem->product_id,
                    'name'         => $cItem->product->name,
                    'price'        => $cItem->unit_price,
                    'quantity'     => $cItem->quantity,
                    'image'        => $cItem->product->images->first()->image_url ?? 'default.jpg'
                ];
            }

            // Ghi nhận danh sách sản phẩm thanh toán vào bộ nhớ Session
            session(['checkout_items' => $checkoutItems]);
        }

        /*
        |--------------------------------------------------------------------------
        | LUỒNG B: XỬ LÝ ĐƠN HÀNG "MUA NGAY" (NẾU KHÔNG CÓ DỮ LIỆU TỪ GIỎ)
        |--------------------------------------------------------------------------
        */
        elseif (session()->has('checkout_items')) {
            $checkoutItems = session('checkout_items');
        }

        /*
        |--------------------------------------------------------------------------
        | LUỒNG C: PHƯƠNG ÁN DỰ PHÒNG - KHÔNG CÓ DỮ LIỆU THANH TOÁN
        |--------------------------------------------------------------------------
        */
        else {
            return redirect()
                ->route('user.cart.index')
                ->with('error', 'Chưa có thiết bị nào được chọn để điều động!');
        }

        // --- TÍNH TỔNG GIÁ TRỊ ĐƠN HÀNG ---
        foreach ($checkoutItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // --- XỬ LÝ SỔ ĐỊA CHỈ GIAO HÀNG ---
        // Lấy danh sách địa chỉ nhận hàng của khách, ưu tiên đưa địa chỉ mặc định lên đầu
        $addresses = Address::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->get();

        // Xác định địa chỉ mặc định để tự động điền (Pre-fill) giúp khách hàng thao tác nhanh hơn
        $defaultAddress = $addresses->firstWhere('is_default', 1) ?? $addresses->first();

        return view(
            'User.orders.checkout',
            compact('checkoutItems', 'total', 'addresses', 'defaultAddress')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 3. XỬ LÝ HOÀN TẤT ĐẶT HÀNG (PLACE ORDER)
    |--------------------------------------------------------------------------
    | Chức năng: Khởi tạo đơn hàng, trừ tiền ví, kiểm tra và trừ kho thực tế.
    | Toàn bộ quá trình được bảo vệ nghiêm ngặt bằng Database Transaction.
    */
    public function placeOrder(Request $request)
    {
        $userId = Auth::id();
        $checkoutItems = session('checkout_items');

        // Phòng thủ dữ liệu: Đảm bảo người dùng đã đăng nhập và phiên thanh toán hợp lệ
        if (!$userId || empty($checkoutItems)) {
            return redirect()
                ->route('user.cart.index')
                ->with('error', 'Lỗi kết nối dữ liệu. Vui lòng thử lại.');
        }

        // Kiểm định thông tin đầu vào: Bắt buộc chọn địa chỉ nhận hàng và phương thức thanh toán hợp lệ
        $request->validate([
            'address_id'     => 'required|exists:addresses,id',
            'payment_method' => 'required|in:wallet,cash'
        ]);

        try {
            // [AN TOÀN HỆ THỐNG]: Kích hoạt Transaction. Nếu một trong các bước dưới đây bị lỗi, 
            // toàn bộ dữ liệu sẽ được khôi phục nguyên trạng (Rollback), không lo mất tiền hay lỗi kho.
            DB::beginTransaction();

            // Xác thực và lấy thông tin địa chỉ giao hàng được chọn
            $address = Address::where('user_id', $userId)->findOrFail($request->address_id);

            // Tính tổng số tiền cần thanh toán thực tế của đơn hàng
            $subtotal = 0;
            foreach ($checkoutItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | PHƯƠNG THỨC: THANH TOÁN QUA VÍ ĐIỆN TỬ V-PAY
            |--------------------------------------------------------------------------
            */
            if ($request->payment_method === 'wallet') {
                // [KHÓA HÀNG ĐỢI]: Sử dụng lockForUpdate() để khóa dòng số dư ví của tài khoản này lại,
                // ngăn chặn tuyệt đối lỗi "Double Spending" (người dùng bấm nút thanh toán liên tiếp 2 lần cùng lúc)
                $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

                if (!$wallet) {
                    throw new \Exception('Không tìm thấy Ví Vanguard của bạn!');
                }

                // Kiểm tra ngân sách: Nếu số dư tài khoản nhỏ hơn giá trị đơn hàng thì chặn lại ngay
                if ($wallet->balance < $subtotal) {
                    throw new \Exception(
                        'Số dư ví V-Pay không đủ! Ngân sách yêu cầu: '
                        . number_format($subtotal, 0, ',', '.')
                        . '₫. Vui lòng nạp thêm.'
                    );
                }

                // Trừ tiền trực tiếp trên tài khoản ví điện tử của khách hàng
                $wallet->balance -= $subtotal;
                $wallet->save();
            }

            /*
            |--------------------------------------------------------------------------
            | KHỞI TẠO ĐƠN HÀNG (ORDER RECORD)
            |--------------------------------------------------------------------------
            */
            $order = new Order();
            $order->user_id = $userId;
            $order->order_code = 'VG-' . strtoupper(Str::random(8)); // Tạo mã đơn hàng độc bản (Ví dụ: VG-X87K9LQA)
            $order->subtotal = $subtotal;
            $order->total = $subtotal;
            $order->status = 'pending'; // Trạng thái chờ xử lý ban đầu
            $order->address_id = $address->id;

            /*
            |--------------------------------------------------------------------------
            | CƠ CHẾ SNAPSHOT ĐỊA CHỈ (QUAN TRỌNG ĐỂ LƯU VẾT LỊCH SỬ)
            |--------------------------------------------------------------------------
            | Sao chép cứng toàn bộ thông tin địa chỉ tại thời điểm mua hàng vào bảng Đơn hàng.
            | Mục đích: Sau này nếu khách hàng có sửa đổi hoặc xóa địa chỉ trong sổ địa chỉ cá nhân,
            | thông tin in trên hóa đơn giao UAV này vẫn giữ nguyên, phục vụ chính xác cho khâu vận chuyển.
            */
            $order->shipping_full_name = $address->full_name;
            $order->shipping_phone     = $address->phone;
            $order->shipping_province  = $address->province;
            $order->shipping_district  = $address->district;
            $order->shipping_ward      = $address->ward;
            $order->shipping_street    = $address->street;

            $order->save();

            // Nếu thanh toán bằng ví, ghi nhận lịch sử biến động số dư tài khoản ngay sau khi tạo đơn thành công
            if ($request->payment_method === 'wallet') { 
                Transaction::create([
                    'wallet_id'      => $wallet->id,
                    'type'           => 'payment',
                    'amount'         => $subtotal,
                    'reference_code' => $order->order_code,
                    'status'         => 'success'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | XỬ LÝ CHI TIẾT ĐƠN HÀNG VÀ ĐỒNG BỘ KHO HÀNG THỰC TẾ
            |--------------------------------------------------------------------------
            */
            foreach ($checkoutItems as $item) {
                // [BẢO VỆ TRANH CHẤP KHO]: Khóa dữ liệu sản phẩm này lại bằng lockForUpdate() 
                // để đảm bảo số lượng tồn kho không bị sai lệch nếu có hàng trăm người đặt mua cùng một giây.
                $product = Product::lockForUpdate()->find($item['product_id']);

                // Kiểm tra tồn kho tại thời điểm bấm nút chốt đơn cuối cùng
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception(
                        'Thiết bị ' . $item['name'] . ' không đủ số lượng trong kho.'
                    );
                }

                // Lưu chi tiết sản phẩm, số lượng và mức giá đã chốt của đơn hàng này
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['price'],
                    'total_price' => $item['price'] * $item['quantity']
                ]);

                // Khấu trừ số lượng hàng trong kho của hệ thống tương ứng với số lượng khách mua
                $product->decrement('stock', $item['quantity']);

                // Dọn dẹp vệ sinh giỏ hàng: Nếu món đồ này được chọn từ giỏ, tiến hành xóa bỏ nó khỏi giỏ
                if (isset($item['is_cart']) && $item['is_cart'] == true) {
                    CartItem::where('id', $item['cart_item_id'])->delete();
                }
            }

            // [HOÀN TẤT GIAO DỊCH]: Mọi thứ hoàn hảo -> Lưu vĩnh viễn tất cả thay đổi vào cơ sở dữ liệu
            DB::commit();

            // Xóa sạch bộ nhớ đệm thanh toán tạm thời sau khi đã tạo đơn hàng thành công
            session()->forget('checkout_items');

            // Chuyển hướng người dùng về trang Danh sách đơn hàng kèm thông báo chúc mừng
            return redirect()
                ->route('user.orders.index')
                ->with('success', '✔ Khởi tạo  ' . $order->order_code . ' thành công!');

        } catch (\Exception $e) {
            // [CỨU HỘ DỮ LIỆU]: Nếu xảy ra bất kỳ lỗi gì (hết hàng, lỗi ví, mất kết nối...), 
            // hệ thống lập tức hủy bỏ mọi thao tác dở dang, trả lại tiền ví và số lượng kho ban đầu.
            DB::rollBack();

            return back()->with('error', 'CẢNH BÁO: ' . $e->getMessage());
        }
    }
}