<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class OwnerUserSeeder extends Seeder
{
    /**
     * Pre-computed bcrypt hash so the plaintext password is not stored in source.
     * The User model's `hashed` cast detects already-hashed strings (Hash::isHashed)
     * and skips re-hashing, so passing the hash directly is safe.
     */
    private const OWNER_PASSWORD_HASH = '$2y$12$X97WdPUQn3MNq5KhcxdkGeDqa5.e0E4HvD7bbqiZNgzvx4qR7hUA6';

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
                'password' => self::OWNER_PASSWORD_HASH,
                'email_verified_at' => now(),
            ]
        );

        // 4) Assign role (idempotent)
        if (! $user->hasRole('owner')) {
            $user->assignRole($ownerRole);
        }
    }
}
