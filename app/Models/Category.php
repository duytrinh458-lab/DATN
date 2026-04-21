<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Tên bảng trong MySQL của Duy là categories
    protected $table = 'categories';

    protected $fillable = [
        'parent_id', 
        'name', 
        'slug', 
        'image', 
        'is_active', 
        'sort_order'
    ];

    // Một danh mục có nhiều sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}