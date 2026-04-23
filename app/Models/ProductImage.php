<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    // 1. Tắt timestamps mặc định của Laravel (vì nó đòi cả 2 cột)
    public $timestamps = false; 

    protected $fillable = [
        'product_id', 
        'image_url', 
        'position',
        'created_at' // Cho phép lưu cột này thủ công
    ];

    // 2. Tự động thêm ngày tạo khi lưu ảnh mới
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}