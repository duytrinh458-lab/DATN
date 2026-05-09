<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'parent_id',
        'rating',
        'comment',
        'is_approved'
    ];

    // 🔥 SỬA LỖI 500: Tắt timestamps vì DB không có cột updated_at
    public $timestamps = false;

    // user comment
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}