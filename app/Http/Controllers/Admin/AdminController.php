<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    // ==========================================
    // 1. TRANG TỔNG QUAN (DASHBOARD)
    // ==========================================
    public function dashboard()
    {
        /*
        |--------------------------------------------------------------------------
        | CACHE DASHBOARD (ĐÃ GIẢM TTL XUỐNG 30 GIÂY ĐỂ TRÁNH DỮ LIỆU ẢO)
        | Hoặc bạn có thể đổi thành `Cache::rememberForever` kết hợp với Event/Observer
        | để xóa Cache ngay lập tức mỗi khi có thay đổi DB.
        |--------------------------------------------------------------------------
        */
        $stats = Cache::remember('admin_dashboard_stats', 30, function () {

            return [

                /*
                |--------------------------------------------------------------------------
                | THỐNG KÊ (ĐÃ VÁ LỖI ĐẾM SẢN PHẨM BỊ XÓA)
                |--------------------------------------------------------------------------
                */
                // Đếm tất cả sản phẩm đang thực sự hiển thị
                'productCount' => Product::where('status', 'active')->count(),

                'orderCount' => Order::count(),

                'userCount' => User::where('role', 'customer')->count(), // Tránh đếm nhầm Admin

                'commentCount' => DB::table('reviews')->count(),

                /*
                |--------------------------------------------------------------------------
                | ĐƠN HÀNG CHỜ XỬ LÝ
                |--------------------------------------------------------------------------
                */
                'pendingOrders' => Order::where('status', 'pending')->count(),

                /*
                |--------------------------------------------------------------------------
                | DOANH THU
                |--------------------------------------------------------------------------
                */
                'revenue' => Order::where('status', 'delivered')->sum('total'),

                /*
                |--------------------------------------------------------------------------
                | SẢN PHẨM BÁN CHẠY
                |--------------------------------------------------------------------------
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
        | RETURN VIEW
        |--------------------------------------------------------------------------
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
    public function showQRSettings()
    {
        /*
        |--------------------------------------------------------------------------
        | BADGE ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */
        $pendingOrders = Order::where(
            'status',
            'pending'
        )->count();

        return view(
            'Admin.settings.qr',
            compact('pendingOrders')
        );
    }


    public function updateQR(Request $request)
    {
        $request->validate([

            'qr_code' => 'required|image|mimes:jpeg,png,jpg|max:2048',

        ]);


        if ($request->hasFile('qr_code')) {

            $image = $request->file('qr_code');

            $fileName = 'qr-demo.png';

            $image->move(
                public_path('images'),
                $fileName
            );

            return redirect()

                ->back()

                ->with(
                    'success',
                    'Đã cập nhật mã QR ngân hàng mới thành công!'
                );
        }

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
    public function transactions()
    {
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
                'users.email'
            )

            ->orderByDesc(
                'wallet_transactions.created_at'
            )

            ->paginate(10);


        /*
        |--------------------------------------------------------------------------
        | BADGE ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */
        $pendingOrders = Order::where(
            'status',
            'pending'
        )->count();


        return view(
            'Admin.transactions.index',
            compact(
                'transactions',
                'pendingOrders'
            )
        );
    }


    public function updateTransactionStatus(
        Request $request,
        $id
    ) {

        $newStatus = $request->status;

        $transaction = DB::table('wallet_transactions')

            ->where('id', $id)

            ->first();


        if (!$transaction) {

            return back()->with(
                'error',
                'Giao dịch không tồn tại!'
            );
        }


        $oldStatus = $transaction->status;


        if ($oldStatus == $newStatus) {

            return back()->with(
                'success',
                'Trạng thái không thay đổi.'
            );
        }


        try {

            DB::beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | NẠP TIỀN
            |--------------------------------------------------------------------------
            */
            if ($transaction->type == 'deposit') {

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
            | RÚT TIỀN
            |--------------------------------------------------------------------------
            */
            elseif ($transaction->type == 'withdraw') {

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
| THANH TOÁN
|--------------------------------------------------------------------------
*/
elseif ($transaction->type == 'payment') {

    if (
        $oldStatus == 'pending'
        && $newStatus == 'success'
    ) {

        $wallet = DB::table('wallets')
            ->where('id', $transaction->wallet_id)
            ->first();

        if (
            $wallet &&
            $wallet->balance >= $transaction->amount
        ) {

            DB::table('wallets')
                ->where('id', $transaction->wallet_id)
                ->decrement(
                    'balance',
                    $transaction->amount
                );

        } else {

            DB::rollBack();

            return back()->with(
                'error',
                'Khách hàng không còn đủ tiền trong ví!'
            );
        }
    }

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
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */
            DB::table('wallet_transactions')

                ->where('id', $id)

                ->update([

                    'status' => $newStatus

                ]);


            DB::commit();

            return back()->with(
                'success',
                'Đã cập nhật trạng thái giao dịch!'
            );

        } catch (\Exception $e) {

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
public function refunds()
{
    $pendingRefunds = DB::table('refunds')
        ->where('status', 'pending')
        ->count();

    $refunds = DB::table('refunds')

        ->join('users', 'refunds.user_id', '=', 'users.id')

        ->join('orders', 'refunds.order_id', '=', 'orders.id')

        ->select(
            'refunds.*',
            'users.full_name as user_name',
            'users.avatar as user_avatar',   // ⭐ THÊM DÒNG NÀY
            'orders.order_code',
            'orders.total'
        )

        ->orderByDesc('refunds.created_at')
        ->paginate(5);

    $pendingOrders = Order::where('status', 'pending')->count();

    return view('admin.refunds.index', compact(
        'refunds',
        'pendingOrders',
        'pendingRefunds'
    ));
}


public function updateRefundStatus(
    Request $request,
    $id
) {

    $newStatus = $request->status;

    $refund = DB::table('refunds')

        ->where('id', $id)

        ->first();


    if (!$refund) {

        return back()->with(
            'error',
            'Không tìm thấy yêu cầu hoàn trả này!'
        );
    }


    if ($refund->status != 'pending') {

        return back()->with(
            'error',
            'Yêu cầu này đã được xử lý trước đó!'
        );
    }


    try {

        DB::beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | DUYỆT HOÀN TIỀN
        |--------------------------------------------------------------------------
        */
        if ($newStatus == 'approved') {

            $order = DB::table('orders')

                ->where(
                    'id',
                    $refund->order_id
                )

                ->first();


            $wallet = DB::table('wallets')

                ->where(
                    'user_id',
                    $refund->user_id
                )

                ->first();


            if ($wallet && $order) {

                /*
                |--------------------------------------------------------------------------
                | CỘNG TIỀN VỀ VÍ
                |--------------------------------------------------------------------------
                */
                DB::table('wallets')

                    ->where(
                        'id',
                        $wallet->id
                    )

                    ->increment(
                        'balance',
                        $order->total
                    );


                /*
                |--------------------------------------------------------------------------
                | TẠO LỊCH SỬ GIAO DỊCH
                |--------------------------------------------------------------------------
                */
                DB::table('wallet_transactions')

                    ->insert([

                        'wallet_id' => $wallet->id,

                        'type' => 'refund',

                        'amount' => $order->total,

                        'status' => 'success',

                        'created_at' => now()

                    ]);


                /*
                |--------------------------------------------------------------------------
                | UPDATE ĐƠN HÀNG
                |--------------------------------------------------------------------------
                */
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
        | UPDATE REFUND STATUS
        |--------------------------------------------------------------------------
        */
        DB::table('refunds')

            ->where('id', $id)

            ->update([

                'status' => $newStatus

            ]);


        DB::commit();

        return back()->with(
    'success',
    'Đã xử lý yêu cầu hoàn tiền'

        );

    } catch (\Exception $e) {

        DB::rollBack();

        return back()->with(
            'error',
            'Lỗi hệ thống: ' . $e->getMessage()
        );
    }
}

public function badges()
{
    try {

        return response()->json([

            // Đơn hàng chờ xử lý
            'pendingOrders' => Order::where('status', 'pending')->count(),

            // Yêu cầu hoàn hàng
            'pendingRefunds' => DB::table('refunds')
                ->where('status', 'pending')
                ->count(),

            // Reviews (KHÔNG dùng admin_reply để tránh lỗi)
            'reviews' => DB::table('reviews')->count(),

        ]);

    } catch (\Exception $e) {

        return response()->json([
            'error' => true,
            'message' => $e->getMessage()
        ], 500);
    }
}
}