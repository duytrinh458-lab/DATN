<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;


    /*
    |--------------------------------------------------------------------------
    | CUSTOM TIMESTAMPS
    |--------------------------------------------------------------------------
    */
    const CREATED_AT = 'ordered_at';

    // Không dùng updated_at
    const UPDATED_AT = null;

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'order_code',

        'user_id',

        'address_id',

        'subtotal',

        'shipping_fee',

        'discount',

        'total',

        'status',

        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT SHIPPING
        |--------------------------------------------------------------------------
        */
        'shipping_full_name',

        'shipping_phone',

        'shipping_province',

        'shipping_district',

        'shipping_ward',

        'shipping_street',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function orderItems()
    {
        return $this->hasMany(
            OrderItem::class,
            'order_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ADDRESS
    |--------------------------------------------------------------------------
    */
    public function address()
    {
        return $this->belongsTo(
            Address::class,
            'address_id'
        )->withTrashed();
    }


    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    // 💡 Lưu ý: Cần thêm withTrashed() để khi tìm đơn hàng cũ, vẫn lấy được thông tin User kể cả khi User đó đã bị Admin xóa
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();

    }
}