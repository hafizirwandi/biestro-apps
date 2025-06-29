<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPackage extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'price', 'quota', 'is_active'];

    public function wahanas()
    {
        return $this->belongsToMany(Wahana::class, 'ticket_package_wahanas');
    }
}
