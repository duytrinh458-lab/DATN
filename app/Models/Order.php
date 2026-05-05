<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Báo cho Laravel biết cột ngày tạo tên là ordered_at
    const CREATED_AT = 'ordered_at';

    protected $fillable = [
        'order_code', 'user_id', 'address_id', 'subtotal', 
        'shipping_fee', 'discount', 'total', 'status'
    ];

    // ================= RELATIONSHIPS =================

    // Một đơn hàng có nhiều chi tiết đơn hàng (sản phẩm)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Một đơn hàng được giao đến một địa chỉ
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    // Một đơn hàng thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}