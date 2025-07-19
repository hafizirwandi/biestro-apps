<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    private $permissions = [

        'user-list',
        'user-edit',
        'user-delete',
        'user-create',
        'role-list',
        'role-edit',
        'role-delete',
        'role-create',
        'role-add-permission',
        'permission-list',
        'permission-edit',
        'permission-delete',
        'permission-create',
        'wahana-list',
        'wahana-edit',
        'wahana-delete',
        'wahana-create',
        'ticket-list',
        'ticket-edit',
        'ticket-delete',
        'ticket-create',
        'ticket-package-list',
        'ticket-package-edit',
        'ticket-package-delete',
        'ticket-package-create',
        'report-transaction',
        'setting',
        'filemanager-view',
        'audit-trace',
        'report-transaction'
    ];
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach ($this->permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        $role = Role::updateOrCreate(['name' => 'admin']);

        foreach ($this->permissions as $perm) {
            $role->givePermissionTo($perm);
        }
    }
}
