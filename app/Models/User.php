<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Wallet; 

class User extends Authenticatable
{
    use HasFactory, Notifiable; 

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
        'status',
        'is_first_login' // Vẫn giữ nguyên cờ này để đổi mật khẩu lần đầu hoạt động
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_verified' => 'boolean',
        'is_online' => 'boolean',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
}