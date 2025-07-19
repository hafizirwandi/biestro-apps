<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTicketSequence extends Model
{
    use HasFactory;
    protected $fillable = ['wahana_id', 'date', 'last_number'];
}
