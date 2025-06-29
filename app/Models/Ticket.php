<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = ['wahana_id', 'name', 'price', 'quota', 'is_active'];

    public function wahana()
    {
        return $this->belongsTo(Wahana::class);
    }
}
