<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    // Nếu bảng có created_at, updated_at thì bỏ dòng này
    public $timestamps = false;

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

    /**
     * Liên kết với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Liên kết với Order
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'address_id');
    }
}
