<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionFreeGift extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'free_gift_rule_id',
        'quantity',
        'is_claim',
        'claimed_at'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }


    public function freeGiftRule()
    {
        return $this->belongsTo(FreeGiftRule::class);
    }
    public function issuedTickets()
    {
        return $this->hasMany(IssuedTicket::class);
    }
}
