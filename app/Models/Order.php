<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 💡 Đã thêm thư viện

class Order extends Model
{
    use SoftDeletes; // 💡 Đã kích hoạt Xóa mềm

    const CREATED_AT = 'ordered_at';
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

    // 💡 Lưu ý: Cần thêm withTrashed() để khi tìm đơn hàng cũ, vẫn lấy được thông tin User kể cả khi User đó đã bị Admin xóa
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}