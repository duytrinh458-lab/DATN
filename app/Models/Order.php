<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 💡 THÊM XÓA MỀM (SOFT DELETES)

class Order extends Model
{
    use SoftDeletes; // 💡 KÍCH HOẠT XÓA MỀM

    const CREATED_AT = 'ordered_at';
    
    // 🔥 FIX LỖI ĐỎ: Vẫn giữ nguyên tắt updated_at
    const UPDATED_AT = null; 

    protected $fillable = [
        'order_code', 'user_id', 'address_id', 'subtotal', 
        'shipping_fee', 'discount', 'total', 'status'
    ];

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