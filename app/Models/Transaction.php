<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['visitor_id', 'transaction_code', 'payment_status', 'total_amount', 'bill_count_print', 'paid_at', 'voided_at', 'voided_by', 'void_reason', 'payment_type', 'amount_given', 'noncash_channel', 'noncash_method', 'user_id', 'cashier_shift_id'];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function freeGifts()
    {
        return $this->hasMany(TransactionFreeGift::class);
    }
}
