<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

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

    /*
    |-------------------------------------------------
    | FIX TIMESTAMPS (DB không có updated_at)
    |-------------------------------------------------
    */
    const UPDATED_AT = null;

    /*
    |-------------------------------------------------
    | RELATION: USER
    |-------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |-------------------------------------------------
    | RELATION: PRODUCT
    |-------------------------------------------------
    */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function replies()
{
    return $this->hasMany(self::class, 'parent_id');
}
}