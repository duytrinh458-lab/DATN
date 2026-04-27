<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code', 'user_id', 'address_id', 'subtotal', 
        'shipping_fee', 'discount', 'total', 'status'
    ];
}