<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaygroundSession extends Model
{
    use HasFactory;

    protected $table = 'playground_sessions';

    protected $fillable = [
        'wahana_id',
        'issued_ticket_id',
        'child_name',
        'gender',
        'clothing_color',
        'duration_minutes',
        'started_at',
        'end_at',
        'status',
        'is_calling',
        'call_count',
        'last_called_at',
        'picked_up_at',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'end_at' => 'datetime',
        'last_called_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'is_calling' => 'boolean',
    ];

    public function wahana()
    {
        return $this->belongsTo(Wahana::class);
    }

    public function issuedTicket()
    {
        return $this->belongsTo(IssuedTicket::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function remainingSeconds(): int
    {
        return max(0, $this->end_at->timestamp - now()->timestamp);
    }
}
