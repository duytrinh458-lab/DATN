<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'sku', 'description', 'original_price', 
        'sale_price', 'stock', 'is_featured', 'status', 
        'flight_time', 'max_altitude', 'camera_mp', 'frequency', 'weight'
    ];

    // 🔥 SỬA Ở ĐÂY: 1 sản phẩm có NHIỀU ảnh
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('position');
    }

    // Quan hệ với Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}