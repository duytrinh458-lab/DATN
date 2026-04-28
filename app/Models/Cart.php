<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    // Khai báo bảng tương ứng trong CSDL của Duy
    protected $table = 'carts';

    // Cho phép lưu các cột này
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity'
    ];

    // Thiết lập quan hệ để lấy thông tin UAV
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}