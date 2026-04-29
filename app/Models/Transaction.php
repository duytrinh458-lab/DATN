<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id',       // 🔥 QUAN TRỌNG
        'type',
        'amount',
        'reference_code',
        'status'
    ];

    public $timestamps = false;

    // ================= RELATION =================

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}