<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'sku', 'description', 'original_price',
        'sale_price', 'stock', 'is_featured', 'status', 'flight_time',
        'max_altitude', 'camera_mp', 'frequency', 'weight'
    ];

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

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('position');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}