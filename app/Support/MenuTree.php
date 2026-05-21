<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Site;
use Illuminate\Support\Collection;

class MenuTree
{
    public static function byId(int $menuId): Collection
    {
        return cache()->remember("frontend.menu.tree.id.$menuId", now()->addMinutes(10), function () use ($menuId) {
            $menu = Menu::with(['sites' => function ($q) {
                $q->public()->orderBy('menu_site.menu_order', 'asc');
            }])->find($menuId);

            return self::buildTree($menu?->sites ?? collect());
        });
    }

    public static function byName(string $name): Collection
    {
        $key = 'frontend.menu.tree.name.' . md5(mb_strtolower(trim($name)));

        return cache()->remember($key, now()->addMinutes(10), function () use ($name) {
            $menu = Menu::where('name', $name)
                ->with(['sites' => function ($q) {
                    $q->public()->orderBy('menu_site.menu_order', 'asc');
                }])
                ->first();

            return self::buildTree($menu?->sites ?? collect());
        });
    }

    private static function buildTree(Collection $sites): Collection
    {
        $byParent = $sites->groupBy('parent_id');

        $walk = function ($parentId) use (&$walk, $byParent) {
            return ($byParent[$parentId] ?? collect())->values()->map(function (Site $site) use (&$walk) {
                // Attach children collection so Blade recursion can use $site->children
                $site->setRelation('children', $walk($site->id));
                return $site;
            });
        };

        return $walk(null);
    }
}
