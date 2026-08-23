<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
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
        'free-gift-list',
        'free-gift-edit',
        'free-gift-delete',
        'free-gift-create',
        'counter-list',
        'counter-edit',
        'counter-delete',
        'counter-create',
        'dashboard',
        'setting',
        'pos',
        'filemanager-view',
        'audit-trace',
        'report-transaction',
        'report-ticket',
        'report-revenue',
        'report-popular-wahana',
        'report-ticket-usage',
        'scan-access',
        'scan-unflag',
        'playground-access',
        'playground-input',
        'playground-report',
    ];
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach ($this->permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // Spatie caches the permission list in-memory per request, separate
        // from the Laravel cache store entry cleared above — permissions
        // just created via updateOrCreate() won't be visible to
        // givePermissionTo() below without forcing this cache to reload.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::updateOrCreate(['name' => 'admin']);

        foreach ($this->permissions as $perm) {
            $role->givePermissionTo($perm);
        }

        $scanRole = Role::updateOrCreate(['name' => 'scan']);
        $scanRole->givePermissionTo(['scan-access']);

        $playgroundRole = Role::updateOrCreate(['name' => 'playground']);
        $playgroundRole->givePermissionTo(['playground-access', 'playground-input', 'playground-report']);
    }
}
