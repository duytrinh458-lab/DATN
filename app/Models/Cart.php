<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';

    // Bảng carts bây giờ chỉ lưu mỗi user_id
    protected $fillable = [
        'user_id'
    ];

    // Quan hệ: 1 Giỏ hàng có nhiều Chi tiết sản phẩm
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
}