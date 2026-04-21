<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    // Tên bảng trong Database của Duy là product_images
    protected $table = 'product_images';

    // Bảng này Duy không thiết kế cột created_at và updated_at nên cần tắt nó đi
    public $timestamps = false;

    protected $fillable = [
        'product_id', 
        'image_url1', 
        'image_url2', 
        'image_url3', 
        'image_url4'
    ];

    // Quan hệ ngược lại với Product (nếu cần)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}