<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class IssuedTicket extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'issued_tickets';
    protected $fillable = [
        'transaction_detail_id',
        'ticket_id',
        'ticket_package_id',
        'wahana_id',
        'is_used',
        'used_at',
        'used_by',
        'unflagged_by',
        'unflagged_at',
        'unflag_reason',
        'ticket_code',
        'free_gift_rule_id',
        'transaction_id',
        'count_print',
        'last_printed_at'
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'unflagged_at' => 'datetime',
        'last_printed_at' => 'datetime',
    ];

    public function wahana()
    {
        return $this->belongsTo(Wahana::class);
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function unflaggedBy()
    {
        return $this->belongsTo(User::class, 'unflagged_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('issued_tickets')
            ->logOnly(['is_used', 'used_at', 'used_by', 'unflagged_by', 'unflagged_at', 'unflag_reason'])
            ->logOnlyDirty();
    }
}
