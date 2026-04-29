<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product; // ✔ FIX QUAN TRỌNG

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity'
    ];

    // Quan hệ với sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}