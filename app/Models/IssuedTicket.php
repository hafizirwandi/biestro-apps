<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedTicket extends Model
{
    use HasFactory;
    protected $table = 'issued_tickets';
    protected $fillable = [
        'transaction_detail_id',
        'ticket_id',
        'ticket_package_id',
        'wahana_id',
        'is_used',
        'used_at',
        'ticket_code',
        'free_gift_rule_id',
        'transaction_id',
        'count_print',
        'last_printed_at'
    ];

    public function wahana()
    {
        return $this->belongsTo(Wahana::class);
    }
}
