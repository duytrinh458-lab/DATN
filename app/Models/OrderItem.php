<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    // Tắt tự động lưu created_at và updated_at vì CSDL không có
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price', 'total_price'
    ];

    // ================= RELATIONSHIPS =================

    // Chi tiết đơn hàng thuộc về một sản phẩm cụ thể
    public function product()
    {
        // 💡 ĐÃ SỬA: Thêm ->withTrashed() để hiển thị được tên UAV kể cả khi Admin đã xóa mềm
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    // Chi tiết đơn hàng thuộc về một đơn hàng tổng
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}