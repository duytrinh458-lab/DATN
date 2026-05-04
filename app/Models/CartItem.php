<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_items';
    
    // Tắt timestamps vì bảng cart_items trong CSDL của bạn không có created_at / updated_at
    public $timestamps = false; 

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price'
    ];

    // Quan hệ với sản phẩm (để ra View còn lấy được tên, ảnh sản phẩm)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}