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

class OrderApiController extends Controller
{
    // =========================
    // DANH SÁCH ĐƠN HÀNG
    // =========================

    public function index()
    {
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

    // =========================
    // CHI TIẾT ĐƠN HÀNG
    // =========================

    public function show($id)
    {
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

    // =========================
    // TẠO ĐƠN HÀNG
    // =========================

    public function store(Request $request)
    {
        $userId = Auth::id();

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

        try {

            DB::beginTransaction();

            // =========================
            // CHECK ADDRESS
            // =========================

            $address = Address::where(
                    'id',
                    $request->address_id
                )
                ->where('user_id', $userId)
                ->first();

            if (!$address) {
                throw new \Exception(
                    'Địa chỉ giao hàng không hợp lệ.'
                );
            }

            // =========================
            // SHIPPING FEE
            // =========================

            $province = mb_strtolower(
                $address->province,
                'UTF-8'
            );

            $shippingFee =
                (
                    str_contains($province, 'hà nội') ||
                    str_contains($province, 'hồ chí minh')
                )
                ? 30000
                : 50000;

            // =========================
            // CART ITEMS
            // =========================

            $cartItems = DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->get();

            $subtotal = 0;

            foreach ($cartItems as $item) {

                $subtotal +=
                    $item->unit_price *
                    $item->quantity;
            }

            // =========================
            // WALLET CHECK
            // =========================

            $totalAmountToPay =
                $subtotal + $shippingFee;

            $wallet = Wallet::where(
                    'user_id',
                    $userId
                )
                ->lockForUpdate()
                ->first();

            if (
                !$wallet ||
                $wallet->balance < $totalAmountToPay
            ) {
                throw new \Exception(
                    'Số dư ví V-Pay không đủ!'
                );
            }

            $wallet->balance -= $totalAmountToPay;

            $wallet->save();

            // =========================
            // CREATE ORDER
            // =========================

            $order = Order::create([

                'user_id'      => $userId,

                'address_id'   => $request->address_id,

                'order_code'   =>
                    'VG-' .
                    strtoupper(Str::random(8)),

                'subtotal'     => $subtotal,

                'shipping_fee' => $shippingFee,

                'total'        => $totalAmountToPay,

                'status'       => 'pending'
            ]);

            // =========================
            // LOAD PRODUCTS
            // =========================

            $productIds = $cartItems->pluck(
                'product_id'
            );

            $products = Product::whereIn(
                    'id',
                    $productIds
                )
                ->get()
                ->keyBy('id');

            // =========================
            // CREATE ORDER ITEMS
            // =========================

            foreach ($cartItems as $item) {

                $product =
                    $products[$item->product_id]
                    ?? null;

                if (
                    !$product ||
                    $product->stock < $item->quantity
                ) {
                    throw new \Exception(
                        'Sản phẩm ' .
                        ($product
                            ? $product->name
                            : '') .
                        ' không đủ hàng!'
                    );
                }

                OrderItem::create([

                    'order_id'   => $order->id,

                    'product_id' => $item->product_id,

                    'quantity'   => $item->quantity,

                    'unit_price' => $item->unit_price,

                    'total_price' =>
                        $item->unit_price *
                        $item->quantity
                ]);

                $product->decrement(
                    'stock',
                    $item->quantity
                );
            }

            // =========================
            // WALLET TRANSACTION
            // =========================

            DB::table('wallet_transactions')
                ->insert([

                    'wallet_id' =>
                        $wallet->id,

                    'type' =>
                        'payment',

                    'amount' =>
                        $totalAmountToPay,

                    'reference_code' =>
                        $order->order_code,

                    'status' =>
                        'success',

                    'created_at' =>
                        now()
                ]);

            // =========================
            // CLEAR CART
            // =========================

            DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->delete();

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' =>
                    'Đặt hàng thành công!',

                'data'    => $order

            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            $response = [

                'status'  => false,

                'message' =>
                    'Có lỗi xảy ra khi tạo đơn hàng!'
            ];

            if (config('app.debug')) {

                $response['error'] =
                    $e->getMessage();
            }

            return response()->json(
                $response,
                400
            );
        }
    }

    // =========================
    // HỦY ĐƠN HÀNG
    // =========================

    public function cancel($id)
    {
        $order = Order::where(
                'id',
                $id
            )
            ->where(
                'user_id',
                Auth::id()
            )
            ->first();

        if (!$order) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy đơn hàng!'
            ], 404);
        }

        if (
            in_array(
                $order->status,
                [
                    'cancelled',
                    'delivered',
                    'shipping'
                ]
            )
        ) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Đơn hàng này không thể hủy!'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $wallet = Wallet::where(
                    'user_id',
                    Auth::id()
                )
                ->lockForUpdate()
                ->first();

            if ($wallet) {

                $wallet->balance +=
                    $order->total;

                $wallet->save();

                DB::table(
                    'wallet_transactions'
                )->insert([

                    'wallet_id' =>
                        $wallet->id,

                    'type' =>
                        'refund',

                    'amount' =>
                        $order->total,

                    'reference_code' =>
                        'REF-' .
                        $order->id .
                        '-' .
                        time(),

                    'status' =>
                        'completed',

                    'created_at' =>
                        now()
                ]);
            }

            $order->status = 'cancelled';

            $order->save();

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' =>
                    'Hủy đơn hàng thành công!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            $response = [

                'status'  => false,

                'message' =>
                    'Có lỗi xảy ra!'
            ];

            if (config('app.debug')) {

                $response['error'] =
                    $e->getMessage();
            }

            return response()->json(
                $response,
                500
            );
        }
    }

    // =========================
    // LẤY STATUS
    // =========================

    public function getStatus($id)
    {
        $user = Auth::user();

        $query = Order::select(
                'id',
                'user_id',
                'status',
                'order_code'
            )
            ->where('id', $id);

        if ($user->role !== 'admin') {

            $query->where(
                'user_id',
                $user->id
            );
        }

        $order = $query->first();

        if (!$order) {

            return response()->json([

                'status' => false,

                'message' =>
                    'Không tìm thấy đơn hàng!'
            ], 404);
        }

        return response()->json([

            'status' => true,

            'data' => [

                'order_code' =>
                    $order->order_code,

                'status' =>
                    $order->status
            ]
        ]);
    }

    // =========================
    // TIMELINE
    // =========================

    public function getTimeline($id)
    {
        $user = Auth::user();

        $query = Order::where(
            'id',
            $id
        );

        if ($user->role !== 'admin') {

            $query->where(
                'user_id',
                $user->id
            );
        }

        $order = $query->first();

        if (!$order) {

            return response()->json([

                'status' => false,

                'message' =>
                    'Không tìm thấy đơn hàng!'
            ], 404);
        }

        $timeline = [

            [
                'time' =>
                    $order->created_at,

                'desc' =>
                    'Đơn hàng đã được tạo'
            ],

            [
                'time' =>
                    $order->updated_at,

                'desc' =>
                    'Trạng thái hiện tại: ' .
                    $order->status
            ]
        ];

        return response()->json([

            'status' => true,

            'data' => $timeline
        ]);
    }

    // =========================
    // XÁC NHẬN ĐÃ NHẬN HÀNG
    // =========================

    public function confirmReceived($id)
    {
        $order = Order::where(
                'id',
                $id
            )
            ->where(
                'user_id',
                Auth::id()
            )
            ->first();

        if (!$order) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy đơn hàng!'
            ], 404);
        }

        if ($order->status !== 'shipping') {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Đơn hàng chưa được giao!'
            ], 400);
        }

        $order->status = 'delivered';

        $order->save();

        return response()->json([

            'status'  => true,

            'message' =>
                'Xác nhận nhận hàng thành công!'
        ]);
    }

    // =========================
    // YÊU CẦU HOÀN TIỀN
    // =========================

    public function requestRefund(
        Request $request,
        $id
    ) {

        $request->validate([

            'reason' =>
                'required|string|max:500'
        ]);

        $order = Order::where(
                'id',
                $id
            )
            ->where(
                'user_id',
                Auth::id()
            )
            ->first();

        if (!$order) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy đơn hàng!'
            ], 404);
        }

        if (
            !in_array(
                $order->status,
                [
                    'shipping',
                    'delivered'
                ]
            )
        ) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Đơn hàng chưa hợp lệ để hoàn tiền!'
            ], 400);
        }

        $alreadyRequested =
            Refund::where(
                'order_id',
                $order->id
            )->exists();

        if ($alreadyRequested) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Yêu cầu hoàn tiền đã tồn tại!'
            ], 400);
        }

        Refund::create([

            'order_id'      =>
                $order->id,

            'user_id'       =>
                Auth::id(),

            'refund_amount' =>
                $order->total,

            'reason'        =>
                $request->reason,

            'status'        =>
                'pending'
        ]);

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã gửi yêu cầu hoàn tiền!'
        ]);
    }

    // =========================
    // ADMIN UPDATE STATUS
    // =========================

    public function adminUpdateStatus(
        Request $request,
        $id
    ) {

        $request->validate([

            'status' =>
                'required|in:pending,processing,shipping,delivered,cancelled'
        ]);

        $order = Order::find($id);

        if (!$order) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không thấy đơn hàng'
            ], 404);
        }

        $order->status =
            $request->status;

        $order->save();

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã cập nhật trạng thái đơn hàng'
        ]);
    }

    // =========================
    // ADMIN APPROVE REFUND
    // =========================

    public function adminApproveRefund(
        $refund_id
    ) {

        try {

            DB::beginTransaction();

            $refund = Refund::where(
                    'id',
                    $refund_id
                )
                ->lockForUpdate()
                ->first();

            if (
                !$refund ||
                $refund->status != 'pending'
            ) {

                throw new \Exception(
                    'Yêu cầu không hợp lệ'
                );
            }

            $wallet = Wallet::where(
                'user_id',
                $refund->user_id
            )->first();

            if ($wallet) {

                $wallet->balance +=
                    $refund->refund_amount;

                $wallet->save();

                DB::table(
                    'wallet_transactions'
                )->insert([

                    'wallet_id' =>
                        $wallet->id,

                    'type' =>
                        'refund',

                    'amount' =>
                        $refund->refund_amount,

                    'reference_code' =>
                        'REF-' .
                        $refund->order_id,

                    'status' =>
                        'success',

                    'created_at' =>
                        now()
                ]);
            }

            $refund->status = 'approved';

            $refund->refunded_at = now();

            $refund->save();

            Order::where(
                'id',
                $refund->order_id
            )->update([
                'status' => 'cancelled'
            ]);

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' =>
                    'Đã duyệt hoàn tiền!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            $response = [

                'status'  => false,

                'message' =>
                    'Có lỗi xảy ra!'
            ];

            if (config('app.debug')) {

                $response['error'] =
                    $e->getMessage();
            }

            return response()->json(
                $response,
                400
            );
        }
    }

    // =========================
    // EDIT NOTE
    // =========================

    public function editNote(
        Request $request,
        $id
    ) {

        $request->validate([

            'note' =>
                'nullable|string|max:500'
        ]);

        $order = Order::where(
                'id',
                $id
            )
            ->where(
                'user_id',
                Auth::id()
            )
            ->first();

        if (!$order) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không tìm thấy đơn hàng!'
            ], 404);
        }

        if (
            !in_array(
                $order->status,
                [
                    'pending',
                    'processing'
                ]
            )
        ) {

            return response()->json([

                'status'  => false,

                'message' =>
                    'Không thể sửa ghi chú!'
            ], 400);
        }

        $order->note =
            $request->note;

        $order->save();

        return response()->json([

            'status'  => true,

            'message' =>
                'Đã cập nhật ghi chú!',

            'data' => [

                'order_id' =>
                    $order->id,

                'note' =>
                    $order->note
            ]
        ]);
    }
}