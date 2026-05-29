<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Laravel\Sanctum\HasApiTokens;

use App\Models\Wallet;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        'is_first_login'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'deleted_at' => 'datetime',

        'is_verified' => 'boolean',
        'is_online' => 'boolean',

        'password' => 'hashed',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
}