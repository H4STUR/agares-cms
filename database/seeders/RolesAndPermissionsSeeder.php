<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // --- Define permissions (single source of truth: App\Support\Permissions) ---
        foreach (Permissions::all() as $p) {
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

        // Owner: belt-and-suspenders alongside Gate::before in AuthServiceProvider.
        $ownerRole->syncPermissions(Permission::all());

        // Admin: everything admin-side (no customer perms)
        $adminRole->syncPermissions([
            Permissions::VIEW_ADMIN_PANEL,
            Permissions::MANAGE_DASHBOARD,

            Permissions::VIEW_USERS, Permissions::MANAGE_USERS,
            Permissions::VIEW_PERMISSIONS, Permissions::MANAGE_PERMISSIONS,

            Permissions::VIEW_SITES, Permissions::MANAGE_SITES,
            Permissions::VIEW_MENUS, Permissions::MANAGE_MENUS,
            Permissions::VIEW_MEDIA, Permissions::MANAGE_MEDIA,
            Permissions::VIEW_SETTINGS, Permissions::MANAGE_SETTINGS,
            Permissions::VIEW_CUSTOM, Permissions::MANAGE_CUSTOM,

            Permissions::VIEW_ARTICLES, Permissions::MANAGE_ARTICLES,
            Permissions::VIEW_CATEGORIES, Permissions::MANAGE_CATEGORIES,

            Permissions::VIEW_COOKIES, Permissions::MANAGE_COOKIES,
            Permissions::VIEW_FORUM, Permissions::MANAGE_FORUM,
            Permissions::VIEW_API, Permissions::MANAGE_API,
            Permissions::VIEW_TOOLS, Permissions::MANAGE_TOOLS,

            Permissions::VIEW_ECOMMERCE, Permissions::MANAGE_ECOMMERCE,
            Permissions::VIEW_ORDERS, Permissions::MANAGE_ORDERS,

            Permissions::VIEW_NOTIFICATIONS,
            Permissions::VIEW_UNPUBLISHED,
            Permissions::ADMIN_NAV,
        ]);

        // Moderator: edit content, no system
        $moderatorRole->syncPermissions([
            Permissions::VIEW_ADMIN_PANEL,
            Permissions::MANAGE_DASHBOARD,

            Permissions::VIEW_SITES,
            Permissions::VIEW_MENUS,
            Permissions::VIEW_MEDIA, Permissions::MANAGE_MEDIA,

            Permissions::VIEW_ARTICLES, Permissions::MANAGE_ARTICLES,
            Permissions::VIEW_CATEGORIES, Permissions::MANAGE_CATEGORIES,

            Permissions::VIEW_NOTIFICATIONS,
            Permissions::VIEW_UNPUBLISHED,
            Permissions::ADMIN_NAV,
        ]);

        // Regular user: no admin panel access
        $userRole->syncPermissions([]);

        // Viewer/demo: read-only across the whole admin
        $viewerRole->syncPermissions(Permissions::viewerSet());

        // Customer: ecommerce shoppers — no admin panel access
        $customerRole->syncPermissions([
            Permissions::PLACE_ORDERS,
            Permissions::VIEW_OWN_ORDERS,
        ]);
    }
}
