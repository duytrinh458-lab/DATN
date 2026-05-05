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
    // Bước 1: Nhận sản phẩm từ nút "Mua ngay"
    public function buyNow(Request $request)
    {
        $product = Product::with('images')->findOrFail($request->product_id);

        session(['checkout_items' => [
            [
                'is_buy_now' => true,
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->sale_price,
                'quantity'   => $request->quantity ?? 1,
                'image'      => $product->images->first()->image_url ?? 'default.jpg'
            ]
        ]]);

        return redirect()->route('user.checkout.index');
    }

    // Bước 2: Hiển thị giao diện thanh toán
    public function index(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) return redirect()->route('login')->with('error', 'Đặc vụ cần đăng nhập để thực hiện nhiệm vụ.');

        $checkoutItems = [];
        $total = 0;

        // ĐÃ SỬA LỖI Ở ĐÂY: Ưu tiên 1 - Lấy dữ liệu từ Giỏ hàng gửi sang trước
        if ($request->has('items') && is_array($request->items)) {
            $cartItems = CartItem::with('product.images')
                ->whereHas('cart', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->whereIn('id', $request->items)
                ->get();

            foreach ($cartItems as $cItem) {
                $checkoutItems[] = [
                    'is_cart'      => true,
                    'cart_item_id' => $cItem->id,
                    'product_id'   => $cItem->product_id,
                    'name'         => $cItem->product->name,
                    'price'        => $cItem->unit_price,
                    'quantity'     => $cItem->quantity,
                    'image'        => $cItem->product->images->first()->image_url ?? 'default.jpg'
                ];
            }
            // Ghi đè lại session bằng danh sách đồ mới chọn
            session(['checkout_items' => $checkoutItems]);
        } 
        // Ưu tiên 2: Nếu không có từ giỏ hàng, thì kiểm tra xem có đi từ nút "Mua ngay" không
        elseif (session()->has('checkout_items')) {
            $checkoutItems = session('checkout_items');
        } 
        else {
            return redirect()->route('user.cart.index')->with('error', 'Chưa có thiết bị nào được chọn để điều động!');
        }

        // Tính tổng tiền
        foreach($checkoutItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Lấy danh sách Địa chỉ
        $addresses = Address::where('user_id', $userId)->orderByDesc('is_default')->get();
        $defaultAddress = $addresses->firstWhere('is_default', 1) ?? $addresses->first();

        return view('User.orders.checkout', compact('checkoutItems', 'total', 'addresses', 'defaultAddress')); 
    }

    // Bước 3: Xử lý khi khách bấm "Xác nhận thanh toán"
    // Bước 3: Xử lý khi khách bấm "Xác nhận thanh toán"
    public function placeOrder(Request $request)
    {
        $userId = Auth::id();
        $checkoutItems = session('checkout_items');

        if (!$userId || empty($checkoutItems)) {
            return redirect()->route('user.cart.index')->with('error', 'Lỗi kết nối dữ liệu. Vui lòng thử lại.');
        }

        // Validate địa chỉ và phương thức thanh toán
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:wallet,cash'
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach($checkoutItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            // ==========================================
            // XỬ LÝ THANH TOÁN VÍ V-PAY (NẾU CHỌN)
            // ==========================================
            if ($request->payment_method === 'wallet') {
                // Khóa dòng dữ liệu ví để tránh lỗi trừ tiền cùng lúc
                $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
                
                if (!$wallet) {
                    throw new \Exception('Không tìm thấy Ví Vanguard của bạn!');
                }
                
                if ($wallet->balance < $subtotal) {
                    throw new \Exception('Số dư ví V-Pay không đủ! Ngân sách yêu cầu: ' . number_format($subtotal, 0, ',', '.') . '₫. Vui lòng nạp thêm.');
                }

                // Trừ tiền trong ví
                $wallet->balance -= $subtotal;
                $wallet->save();
            }

            // ==========================================
            // 1. Tạo đơn hàng (Order)
            // ==========================================
            $order = new Order();
            $order->user_id    = $userId;
            $order->order_code = 'VG-' . strtoupper(Str::random(8));
            $order->subtotal   = $subtotal;
            $order->total      = $subtotal; 
            $order->status     = 'pending'; 
            $order->address_id = $request->address_id;
            $order->save();

            // ==========================================
            // LƯU LỊCH SỬ THANH TOÁN VÀ GIAO DỊCH VÍ
            // ==========================================
            if ($request->payment_method === 'wallet') {
                // Lưu vào wallet_transactions
                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'payment',
                    'amount' => $subtotal,
                    'reference_code' => $order->order_code,
                    'status' => 'success'
                ]);

                // Lưu vào bảng payments của đơn hàng
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'method' => 'wallet',
                    'status' => 'paid',
                    'amount' => $subtotal,
                    'paid_at' => now()
                ]);
            } else {
                // Nếu chọn COD (Cash)
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'method' => 'cash',
                    'status' => 'pending',
                    'amount' => $subtotal,
                    'paid_at' => null
                ]);
            }

            // ==========================================
            // 2. Lưu chi tiết đơn hàng & Trừ kho
            // ==========================================
            foreach ($checkoutItems as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception('Thiết bị ' . $item['name'] . ' không đủ số lượng trong kho.');
                }

                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['price'],
                    'total_price' => $item['price'] * $item['quantity']
                ]);

                // Trừ kho UAV
                $product->decrement('stock', $item['quantity']);

                // Xóa khỏi giỏ hàng
                if (isset($item['is_cart']) && $item['is_cart'] == true) {
                    CartItem::where('id', $item['cart_item_id'])->delete();
                }
            }

            DB::commit();
            session()->forget('checkout_items'); 

            return redirect()->route('user.orders.index')->with('success', '✔ Khởi tạo lệnh điều động ' . $order->order_code . ' thành công!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'CẢNH BÁO: ' . $e->getMessage());
        }
    }
}