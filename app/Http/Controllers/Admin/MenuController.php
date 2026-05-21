<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with(['sites' => function ($q) {
            $q->orderBy('menu_site.menu_order');
        }])->orderBy('id')->get();

        return view('pages.admin.menus.index', compact('menus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $menu = Menu::create([
            'name'       => trim($validated['name']),
            'is_system'  => false,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Menu created.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->is_system) {
            return back()->with('error', 'This menu is a system menu and cannot be deleted.');
        }

        DB::beginTransaction();

        try {
            // All sites assigned to this menu
            $menuSiteIds = $menu->sites()->pluck('sites.id')->toArray();

            // Expand to include children (site hierarchy)
            $allSiteIds = $this->collectSiteTreeIds($menuSiteIds);

            // Detach all pivot rows first
            DB::table('menu_site')->where('menu_id', $menu->id)->delete();

            // Delete sites (will cascade categories/articles if FK is set, or trigger model deletes)
            if (!empty($allSiteIds)) {
                Site::whereIn('id', $allSiteIds)->delete();
            }

            // Finally delete menu
            $menu->delete();

            DB::commit();
            return back()->with('success', 'Menu (and its pages) deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['menu' => 'Failed to delete menu: ' . $e->getMessage()]);
        }
    }

    /**
     * Collects all descendant site ids for given roots.
     * Uses iterative queries to avoid recursion limits.
     */
    protected function collectSiteTreeIds(array $rootIds): array
    {
        $all = [];
        $queue = array_values(array_unique(array_map('intval', $rootIds)));

        while (!empty($queue)) {
            $chunk = array_splice($queue, 0, 200);

            foreach ($chunk as $id) {
                $all[$id] = true;
            }

            $children = Site::whereIn('parent_id', $chunk)->pluck('id')->toArray();

            foreach ($children as $cid) {
                $cid = (int) $cid;
                if (!isset($all[$cid])) {
                    $queue[] = $cid;
                }
            }
        }

        return array_keys($all);
    }
}
