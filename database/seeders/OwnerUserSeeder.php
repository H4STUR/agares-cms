<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Create (or get) the owner role
        $ownerRole = Role::firstOrCreate([
            'name' => 'owner',
            'guard_name' => 'web',
        ]);

        // 2) Give owner all permissions that exist
        $ownerRole->syncPermissions(Permission::all());

        // 3) Create or update the owner user
        $user = User::updateOrCreate(
            ['email' => 'office@agares.co.uk'],
            [
                'username' => 'Danio',
                'name' => 'Danio',
                'surname' => '',
                'email' => 'office@agares.co.uk',
                'phone' => '000000000',
                'password' => Hash::make('QpMcmc39$!Ca3KRR'),
                'email_verified_at' => now(),
            ]
        );

        // 4) Assign role (idempotent)
        if (! $user->hasRole('owner')) {
            $user->assignRole($ownerRole);
        }
    }
}
