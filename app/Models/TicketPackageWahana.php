<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPackageWahana extends Model
{
    use HasFactory;
    protected $table = 'ticket_package_wahanas';
    protected $fillable = [
        'ticket_package_id',
        'wahana_id',
        'qty'

    ];
}
