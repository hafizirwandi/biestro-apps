<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

class Role extends Model
{
    use HasFactory;
    protected $table = "roles";
    protected $fillable = [
        'name',
        'guard_name',
    ];
    public function permission()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }
    public function getPermissionWithChecked($id)
    {
        return DB::table('permissions as a')
            ->leftJoin('role_has_permissions as b', function (JoinClause $join) use ($id) {
                $join->on('a.id', '=', 'b.permission_id')
                    ->where('b.role_id', $id);
            })
            ->select('a.*', 'b.permission_id as check_id')
            ->get();
    }
}
