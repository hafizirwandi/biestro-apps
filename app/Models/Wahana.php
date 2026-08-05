<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wahana extends Model
{
    use HasFactory;
    protected $table = "wahanas";
    protected $fillable = [
        'key',
        'name',
        'description'
    ];
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketPackages()
    {
        return $this->belongsToMany(TicketPackage::class, 'ticket_package_wahanas');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_wahanas');
    }
}
