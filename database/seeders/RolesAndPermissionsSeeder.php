<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // --- Define permissions ---
        // Rule: for each module we create BOTH "view X" and "manage X"
        $permissions = [
            // Admin core
            ['name' => 'view admin panel', 'category' => 'admin'],
            ['name' => 'manage dashboard', 'category' => 'admin'],
            ['name' => 'manage ecommerce', 'category' => 'admin'],
            ['name' => 'manage forum', 'category' => 'admin'],
            ['name' => 'manage API', 'category' => 'admin'],

            // Ecommerce (customer-facing)
            ['name' => 'place orders', 'category' => 'ecommerce'],
            ['name' => 'view own orders', 'category' => 'ecommerce'],

            // Users / permissions
            ['name' => 'view users', 'category' => 'users'],
            ['name' => 'manage users', 'category' => 'users'],
            ['name' => 'view permissions', 'category' => 'users'],
            ['name' => 'manage permissions', 'category' => 'users'],

            // CMS modules
            ['name' => 'view sites', 'category' => 'cms'],
            ['name' => 'manage sites', 'category' => 'cms'],
            ['name' => 'view menus', 'category' => 'cms'],
            ['name' => 'manage menus', 'category' => 'cms'],
            ['name' => 'view media', 'category' => 'cms'],
            ['name' => 'manage media', 'category' => 'cms'],
            ['name' => 'view settings', 'category' => 'cms'],
            ['name' => 'manage settings', 'category' => 'cms'],
            ['name' => 'view custom', 'category' => 'cms'],
            ['name' => 'manage custom', 'category' => 'cms'],
            ['name' => 'view unpublished content', 'category' => 'cms'],
            ['name' => 'admin nav', 'category' => 'cms'],

            // Content
            ['name' => 'view articles', 'category' => 'content'],
            ['name' => 'manage articles', 'category' => 'content'],
            ['name' => 'view categories', 'category' => 'content'],
            ['name' => 'manage categories', 'category' => 'content'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                ['category' => $p['category']]
            );
        }

        // --- Roles ---
        $ownerRole     = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $adminRole     = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $userRole      = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $viewerRole    = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $customerRole  = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Owner gets everything that exists
        $ownerRole->syncPermissions(Permission::all());

        // Admin can manage everything (except you can restrict later)
        $adminRole->syncPermissions([
            'view admin panel',
            'manage dashboard',

            'view users', 'manage users',
            'view permissions', 'manage permissions',

            'view sites', 'manage sites',
            'view menus', 'manage menus',
            'view media', 'manage media',
            'view settings', 'manage settings',
            'view custom', 'manage custom',

            'view articles', 'manage articles',
            'view categories', 'manage categories',

            'view unpublished content',
            'admin nav',
        ]);

        // Moderator: example (edit content but not system)
        $moderatorRole->syncPermissions([
            'view admin panel',
            'manage dashboard',

            'view sites',
            'view menus',
            'view media',

            'view articles', 'manage articles',
            'view categories', 'manage categories',
            'view unpublished content',
            'admin nav',
        ]);

        // User: no admin panel access
        $userRole->syncPermissions([]);

        // Viewer/demo: read-only, no admin panel access
        $viewerRole->syncPermissions([
            'view users',
            'view permissions',

            'view sites',
            'view menus',
            'view media',
            'view settings',
            'view custom',

            'view articles',
            'view categories',

            'view unpublished content',
        ]);

        // Customer: ecommerce shoppers — no admin panel access
        $customerRole->syncPermissions([
            'place orders',
            'view own orders',
        ]);
    }
}
