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

    public $timestamps = true;

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