<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftRule extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'min_purchase', 'is_multiple', 'is_active', 'description'];

    public function wahanas()
    {
        return $this->belongsToMany(Wahana::class, 'free_gift_rule_wahanas')->withPivot('qty');
    }
}
