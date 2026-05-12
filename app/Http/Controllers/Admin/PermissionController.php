<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\RoleSitePermission;
use App\Models\Site;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view permissions', only: ['index', 'editRole', 'edit']),
            new Middleware('can:manage permissions', only: [
                'updateRole', 'assign', 'create', 'delete', 'createRole', 'deleteRole',
            ]),
        ];
    }


    public function index()
    {
        return view('pages.admin.permissions.index', [
            // owner first, then the rest in the order they were created (id asc)
            'roles' => \Spatie\Permission\Models\Role::query()
                ->orderByRaw("CASE WHEN name = 'owner' THEN 0 ELSE 1 END")
                ->orderBy('id')
                ->get(),

            'permissions' => \Spatie\Permission\Models\Permission::all(),
        ]);
    }


    public function edit(Role $role)
    {
        // Safety: owner should never be editable
        if ($role->name === 'owner') {
            return redirect()->route('admin.permissions')
                ->with('error', 'Owner role cannot be edited.');
        }

        $sites = \App\Models\Site::query()
            ->select('id', 'name', 'slug')
            ->orderBy('name') // or id, up to you
            ->get();

        // CMS-wide permissions (everything NOT tied to a site)
        // For now we just pass all and you can filter by category naming later.
        $permissions = \Spatie\Permission\Models\Permission::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('pages.admin.permissions.edit', compact('role', 'sites', 'permissions'));
    }

    public function editRole(Role $role)
    {
        // Owner: no edit page
        if ($role->name === 'owner') {
            return redirect()->route('admin.permissions')->with('error', 'Owner role cannot be edited.');
        }

        $sites = Site::orderBy('name')->get();
        $permissions = Permission::orderBy('category')->orderBy('name')->get();

        // existing per-site rows
        $sitePerms = RoleSitePermission::where('role_id', $role->id)->get()->keyBy('site_id');

        return view('pages.admin.permissions.edit', compact('role', 'sites', 'permissions', 'sitePerms'));
    }

    public function updateRole(Request $request, Role $role)
    {
        if ($role->name === 'owner') {
            return redirect()->route('admin.permissions')->with('error', 'Owner role cannot be edited.');
        }

        $validated = $request->validate([
            // global permissions
            'permissions' => 'array',
            'permissions.*' => 'string',

            // per-site permissions
            'site_permissions' => 'array',
            'site_permissions.*.can_view' => 'nullable|in:0,1',
            'site_permissions.*.can_edit' => 'nullable|in:0,1',
            'site_permissions.*.can_categories' => 'nullable|in:0,1',
            'site_permissions.*.can_articles' => 'nullable|in:0,1',
        ]);

        DB::transaction(function () use ($validated, $role) {

            // 1) Save GLOBAL permissions (Spatie)
            $names = $validated['permissions'] ?? [];
            $existing = Permission::whereIn('name', $names)->pluck('name')->toArray();
            $role->syncPermissions($existing);

            // 2) Save PER-SITE permissions
            $rows = $validated['site_permissions'] ?? [];

            foreach ($rows as $siteId => $flags) {
                RoleSitePermission::updateOrCreate(
                    ['role_id' => $role->id, 'site_id' => (int)$siteId],
                    [
                        'can_view'       => !empty($flags['can_view']),
                        'can_edit'       => !empty($flags['can_edit']),
                        'can_categories' => !empty($flags['can_categories']),
                        'can_articles'   => !empty($flags['can_articles']),
                    ]
                );
            }
        });

        // clear Spatie cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Role updated.');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', 'Permissions updated.');
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'category' => 'nullable|string|max:100',
        ]);
    
        Permission::create([
            'name' => $request->name,
            'category' => $request->category ?? 'general',
            'guard_name' => 'web',
        ]);
    
        return back()->with('success', 'Permission created.');
    }
    

    public function delete(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'Permission deleted.');
    }

    public function createRole(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:roles,name']);
        Role::create(['name' => $request->name, 'guard_name' => 'web']);
        return back()->with('success', 'Role created.');
    }

    public function deleteRole(Role $role)
    {
        if ($role->name === 'owner') {
            return back()->with('error', 'Cannot delete the owner role.');
        }

        $role->delete();
        return back()->with('success', 'Role deleted.');
    }

}
