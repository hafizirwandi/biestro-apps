<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CreateUserAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::UpdateOrcreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@cat.com',
                'status' => '1',
                'password' => Hash::make('admin')
            ]
        );
        $role = Role::UpdateOrcreate(['name' => 'admin'], ['name' => 'admin']);

        // Opsional: Assign role ke user
        $user->assignRole($role);
    }
}
