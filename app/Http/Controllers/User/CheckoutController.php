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
    | BUY NOW
    |--------------------------------------------------------------------------
    */
    public function buyNow(Request $request)
    {
        $product = Product::with('images')
            ->findOrFail($request->product_id);

        session([
            'checkout_items' => [
                [
                    'is_buy_now' => true,
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'price'      => $product->sale_price,
                    'quantity'   => $request->quantity ?? 1,
                    'image'      => $product->images->first()->image_url ?? 'default.jpg'
                ]
            ]
        ]);

        return redirect()->route('user.checkout.index');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECKOUT PAGE
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Đặc vụ cần đăng nhập để thực hiện nhiệm vụ.'
                );
        }

        $checkoutItems = [];

        $total = 0;

        /*
        |--------------------------------------------------------------------------
        | ƯU TIÊN DỮ LIỆU TỪ GIỎ HÀNG
        |--------------------------------------------------------------------------
        */
        if (
            $request->has('items') &&
            is_array($request->items)
        ) {

            $cartItems = CartItem::with('product.images')

                ->whereHas('cart', function ($q) use ($userId) {

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

                    'image'        => $cItem->product
                        ->images
                        ->first()
                        ->image_url ?? 'default.jpg'
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | GHI LẠI SESSION
            |--------------------------------------------------------------------------
            */
            session([
                'checkout_items' => $checkoutItems
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MUA NGAY
        |--------------------------------------------------------------------------
        */
        elseif (session()->has('checkout_items')) {

            $checkoutItems = session('checkout_items');
        }

        /*
        |--------------------------------------------------------------------------
        | KHÔNG CÓ DỮ LIỆU
        |--------------------------------------------------------------------------
        */
        else {

            return redirect()
                ->route('user.cart.index')
                ->with(
                    'error',
                    'Chưa có thiết bị nào được chọn để điều động!'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | TÍNH TỔNG
        |--------------------------------------------------------------------------
        */
        foreach ($checkoutItems as $item) {

            $total += $item['price'] * $item['quantity'];
        }

        /*
        |--------------------------------------------------------------------------
        | ĐỊA CHỈ
        |--------------------------------------------------------------------------
        */
        $addresses = Address::where('user_id', $userId)

            ->orderByDesc('is_default')

            ->get();

        $defaultAddress = $addresses
            ->firstWhere('is_default', 1)
            ?? $addresses->first();

        return view(
            'User.orders.checkout',
            compact(
                'checkoutItems',
                'total',
                'addresses',
                'defaultAddress'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PLACE ORDER
    |--------------------------------------------------------------------------
    */
    public function placeOrder(Request $request)
    {
        $userId = Auth::id();

        $checkoutItems = session('checkout_items');

        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA USER + SESSION
        |--------------------------------------------------------------------------
        */
        if (!$userId || empty($checkoutItems)) {

            return redirect()
                ->route('user.cart.index')
                ->with(
                    'error',
                    'Lỗi kết nối dữ liệu. Vui lòng thử lại.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATE
        |--------------------------------------------------------------------------
        */
        $request->validate([

            'address_id'     => 'required|exists:addresses,id',

            'payment_method' => 'required|in:wallet,cash'

            ]);

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | LẤY ĐỊA CHỈ
            |--------------------------------------------------------------------------
            */
            $address = Address::where('user_id', $userId)

                ->findOrFail($request->address_id);

            /*
            |--------------------------------------------------------------------------
            | TÍNH TỔNG TIỀN
            |--------------------------------------------------------------------------
            */
            $subtotal = 0;

            foreach ($checkoutItems as $item) {

                $subtotal +=
                    $item['price'] * $item['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | THANH TOÁN VÍ
            |--------------------------------------------------------------------------
            */
            if ($request->payment_method === 'wallet') {

                $wallet = Wallet::where('user_id', $userId)

                    ->lockForUpdate()

                    ->first();

                if (!$wallet) {

                    throw new \Exception(
                        'Không tìm thấy Ví Vanguard của bạn!'
                    );
                }

                if ($wallet->balance < $subtotal) {

                    throw new \Exception(
                        'Số dư ví V-Pay không đủ! Ngân sách yêu cầu: '
                        . number_format($subtotal, 0, ',', '.')
                        . '₫. Vui lòng nạp thêm.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | TRỪ TIỀN
                |--------------------------------------------------------------------------
                */
                $wallet->balance -= $subtotal;

                $wallet->save();
            }

            /*
            |--------------------------------------------------------------------------
            | TẠO ORDER
            |--------------------------------------------------------------------------
            */
            $order = new Order();

            /*
            |--------------------------------------------------------------------------
            | THÔNG TIN CƠ BẢN
            |--------------------------------------------------------------------------
            */
            $order->user_id = $userId;

            $order->order_code =
                'VG-' . strtoupper(Str::random(8));

            $order->subtotal = $subtotal;

            $order->total = $subtotal;

            $order->status = 'pending';

            /*
            |--------------------------------------------------------------------------
            | ADDRESS ID
            |--------------------------------------------------------------------------
            */
            $order->address_id = $address->id;

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT ĐỊA CHỈ
            |--------------------------------------------------------------------------
            | Giữ lại địa chỉ kể cả khi user sửa hoặc xóa address
            |--------------------------------------------------------------------------
            */
            $order->shipping_full_name =
                $address->full_name;

            $order->shipping_phone =
                $address->phone;

            $order->shipping_province =
                $address->province;

            $order->shipping_district =
                $address->district;

            $order->shipping_ward =
                $address->ward;

            $order->shipping_street =
                $address->street;

            /*
            |--------------------------------------------------------------------------
            | SAVE ORDER
            |--------------------------------------------------------------------------
            */
            $order->save();

            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */
            

                // Bọc thêm điều kiện if ở đây: Chỉ lưu lịch sử ví nếu người dùng chọn thanh toán bằng ví
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
            | ORDER ITEMS
            |--------------------------------------------------------------------------
            */
            foreach ($checkoutItems as $item) {

                $product = Product::lockForUpdate()

                    ->find($item['product_id']);

                /*
                |--------------------------------------------------------------------------
                | CHECK STOCK
                |--------------------------------------------------------------------------
                */
                if (
                    !$product ||
                    $product->stock < $item['quantity']
                ) {

                    throw new \Exception(
                        'Thiết bị '
                        . $item['name']
                        . ' không đủ số lượng trong kho.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CREATE ORDER ITEM
                |--------------------------------------------------------------------------
                */
                OrderItem::create([

                    'order_id'    => $order->id,

                    'product_id'  => $item['product_id'],

                    'quantity'    => $item['quantity'],

                    'unit_price'  => $item['price'],

                    'total_price' =>
                        $item['price'] * $item['quantity']
                ]);

                /*
                |--------------------------------------------------------------------------
                | TRỪ KHO
                |--------------------------------------------------------------------------
                */
                $product->decrement(
                    'stock',
                    $item['quantity']
                );

                /*
                |--------------------------------------------------------------------------
                | XÓA CART ITEM
                |--------------------------------------------------------------------------
                */
                if (
                    isset($item['is_cart']) &&
                    $item['is_cart'] == true
                ) {

                    CartItem::where(
                        'id',
                        $item['cart_item_id']
                    )->delete();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */
            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | CLEAR SESSION
            |--------------------------------------------------------------------------
            */
            session()->forget('checkout_items');

            return redirect()

                ->route('user.orders.index')

                ->with(
                    'success',
                    '✔ Khởi tạo  '
                    . $order->order_code
                    . ' thành công!'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(

                'error',

                'CẢNH BÁO: ' . $e->getMessage()
            );
        }
    }
}