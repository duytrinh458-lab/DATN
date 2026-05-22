<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📋 Danh sách đơn hàng
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')

            ->select(
                'orders.*',
                'users.full_name',
                'users.phone'
            )

            ->orderBy('orders.id', 'desc')

            ->paginate(5);

        return view(
            'admin.orders.index',
            compact('orders')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 🔍 Chi tiết đơn hàng
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {

            return redirect()
                ->route('admin.orders.index')
                ->with(
                    'error',
                    'Đơn hàng không tồn tại!'
                );
        }

        $items = DB::table('order_items')

            ->join(
                'products',
                'order_items.product_id',
                '=',
                'products.id'
            )

            ->where('order_items.order_id', $id)

            ->select(
                'products.name',
                'order_items.quantity',
                'order_items.unit_price',
                'order_items.total_price'
            )

            ->get();

        return view(
            'admin.orders.show',
            compact('order', 'items')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 🔄 Cập nhật trạng thái đơn hàng
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE
        |--------------------------------------------------------------------------
        */
        $request->validate([

            'status' => [
                'required',
                'in:pending,processing,shipping,delivered,cancelled'
            ]

        ]);


        /*
        |--------------------------------------------------------------------------
        | FIND ORDER
        |--------------------------------------------------------------------------
        */
        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if (!$order) {

            return back()->with(
                'error',
                'Đơn hàng không tồn tại!'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CHẶN ĐỔI TRẠNG THÁI
        |--------------------------------------------------------------------------
        */
        if (
            in_array(
                $order->status,
                ['delivered', 'cancelled']
            )
        ) {

            return back()->with(
                'error',
                'Không thể thay đổi trạng thái của đơn hàng đã giao hoặc đã hủy!'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */
        DB::table('orders')

            ->where('id', $id)

            ->update([

                'status' => $request->status,

                'updated_at' => now()

            ]);


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */
        return back()->with(
            'success',
            'Cập nhật trạng thái đơn hàng thành công'
        );
    }
}