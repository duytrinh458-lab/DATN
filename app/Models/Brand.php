<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    
    // 💡 ĐÃ FIX LOGIC SAI: Thêm fillable và relation cho Model Brand
    protected $fillable = ['name', 'logo'];
    
    public $timestamps = false; // Bỏ timestamps nếu DB không có

    public function products() 
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}