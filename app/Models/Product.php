<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // 🔥 Cho phép insert/update bằng API
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'original_price',
        'sale_price',
        'stock',
        'is_featured',
        'status',
        'flight_time',
        'max_altitude',
        'camera_mp',
        'frequency',
        'weight'
    ];

    // 🔥 Ép kiểu dữ liệu (rất nên có)
    protected $casts = [
        'original_price' => 'float',
        'sale_price'     => 'float',
        'stock'          => 'integer',
        'is_featured'    => 'boolean',
        'flight_time'    => 'integer',
        'max_altitude'   => 'integer',
        'camera_mp'      => 'integer',
        'weight'         => 'float',
    ];

    // 🔥 Quan hệ: 1 sản phẩm có nhiều ảnh
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')
                    ->orderBy('position');
    }

    // 🔥 Quan hệ: thuộc danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}