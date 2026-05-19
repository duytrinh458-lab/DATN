<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'addresses';

    /*
    |--------------------------------------------------------------------------
    | DISABLE TIMESTAMPS
    |--------------------------------------------------------------------------
    */
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'user_id',

        'full_name',

        'phone',

        'street',

        'district',

        'ward',

        'province',

        'is_default'
    ];

    /*
    |--------------------------------------------------------------------------
    | USER RELATION
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER RELATION
    |--------------------------------------------------------------------------
    */
    public function orders()
    {
        return $this->hasMany(
            Order::class,
            'address_id'
        );
    }
}