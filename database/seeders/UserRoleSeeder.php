<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = [
            'super_admin',
            'manager',
            'employee',
        ];

        foreach ($role as $r) {
            $role = Role::create(['name' => $r]);
        }

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $adminRole = Role::where('name', 'super_admin')->first();
        $admin->assignRole($adminRole);
    }
}
