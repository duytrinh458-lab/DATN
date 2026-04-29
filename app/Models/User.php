<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Wallet; // 🔥 thêm dòng này

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Các cột cho phép insert (PHẢI KHỚP DB)
     */
    protected $fillable = [
        'username',
        'full_name',
        'email',
        'phone',
        'password',
        'avatar',
        'is_verified',
        'is_online',
        'last_seen',
        'role',
        'status'
    ];

    /**
     * Ẩn khi trả JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast dữ liệu
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_verified' => 'boolean',
        'is_online' => 'boolean',
    ];

    // ================= WALLET RELATION =================
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
}