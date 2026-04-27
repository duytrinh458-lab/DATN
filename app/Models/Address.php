<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    // Tên bảng trong DB (nếu bạn đặt là addresses thì Laravel tự hiểu, nhưng khai báo cho chắc)
    protected $table = 'addresses';
    public $timestamps = false;

    // Các cột được phép lưu dữ liệu
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'street',
        'district',
        'city',
        'province',
        'is_default'
    ];

    /**
     * Thiết lập liên kết ngược lại với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}