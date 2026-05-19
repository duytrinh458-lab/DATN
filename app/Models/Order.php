<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Báo cho Laravel biết cột ngày tạo tên là ordered_at
    const CREATED_AT = 'ordered_at';
    
    // 🔥 ĐÃ FIX: Tắt hẳn updated_at để Admin cập nhật trạng thái không bị lỗi SQL
    const UPDATED_AT = null; 

    protected $fillable = [
        'order_code', 'user_id', 'address_id', 'subtotal', 
        'shipping_fee', 'discount', 'total', 'status'
    ];

    // ================= RELATIONSHIPS =================

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}