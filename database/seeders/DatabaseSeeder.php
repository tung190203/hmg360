<?php

namespace Database\Seeders;

use App\Core\Permission\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'backend_access' => ['view'],
            'dashboard' => ['view'],
            'tenants' => ['view', 'create', 'update'],
            'modules' => ['view', 'create', 'update', 'toggle'],
            'roles' => ['view', 'create', 'update'],
            'file_manager' => ['view'],
        ] as $module => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(compact('module', 'permission'));
            }
        }

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('Password@123'),
                'status' => User::STATUS_ACTIVE,
                'is_platform_owner' => true,
            ],
        );
    }
}
