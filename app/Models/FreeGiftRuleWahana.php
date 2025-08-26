<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftRuleWahana extends Model
{
    use HasFactory;
    protected $fillable = [
        'free_gift_rule_id',
        'wahana_id',
        'qty'

    ];
}
