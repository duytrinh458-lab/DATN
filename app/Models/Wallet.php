<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    public $timestamps = false; // 🔥 QUAN TRỌNG

    protected $fillable = [
        'user_id',
        'balance'
    ];

    // ================= RELATION =================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }
}