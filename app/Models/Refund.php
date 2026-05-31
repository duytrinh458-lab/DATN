<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'refund_amount', 'reason', 'status', 'refunded_at'
    ];
}
