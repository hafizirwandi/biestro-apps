<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'ticket_package_id',
        'ticket_id',
        'quantity',
        'price_each',
        'subtotal'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticketPackage()
    {
        return $this->belongsTo(TicketPackage::class);
    }

    public function issuedTickets()
    {
        return $this->hasMany(IssuedTicket::class);
    }
}
