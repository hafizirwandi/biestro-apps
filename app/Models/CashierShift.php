<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'counter_id',
        'opened_at',
        'opening_balance',
        'closed_at',
        'closing_balance',
        'system_balance',
        'difference',
        'notes',
        'status',
    ];
    /**
     * Relasi ke User (kasir yang buka shift)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Counter/Loket
     */
    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    /**
     * Relasi ke Sales/Transactions (kalau sudah ada tabel sales)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_shift_id');
    }
}
