<?php

namespace App\Http\Controllers\Frontend\Ecommerce;

use App\Http\Controllers\Frontend\PageController;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Category as EcommerceCategory;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\Site;

class ShopController extends PageController
{
    public function index()
    {
        $shopUrl = EcommerceSetting::where('key', 'shop_url')->value('value');

        if (!$shopUrl) abort(404, 'Shop page is not configured.');

        $site = Site::where('slug', $shopUrl)->first();
        if (!$site) abort(404, 'Shop site not found.');

        $site->load([
            'categories',
            'articles' => function ($q) {
                if (!auth()->check() || !auth()->user()->can('view unpublished content')) {
                    $q->public();
                } else {
                    $q->withTrashed();
                }
                $q->with(['inputInstances.field', 'inputInstances.files']);
            },
            'categories.articles' => function ($q) {
                if (!auth()->check() || !auth()->user()->can('view unpublished content')) {
                    $q->public();
                } else {
                    $q->withTrashed();
                }
                $q->with(['inputInstances.field', 'inputInstances.files']);
            },
        ]);

        $inputs = $this->loadOwnerInputs(Site::class, $site->id);

        $search = request('search');

        $products = Product::where('status', 'published')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when(request('category'), function ($q) {
                $q->whereHas('categories', fn ($q) => $q->where('slug', request('category')));
            })
            ->with(['defaultVariant', 'categories'])
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $ecommerceCategories = EcommerceCategory::withCount([
            'products' => fn ($q) => $q->where('status', 'published'),
        ])->get();

        $data = array_merge([
            'site'                => $site,
            'categories'          => $site->categories,
            'articles'            => $site->articles,
            'content_site'        => $inputs['byVar'],
            'content_site_list'   => $inputs['list'],
            'content'             => $inputs['byVar'],
            'content_list'        => $inputs['list'],
            'products'            => $products,
            'ecommerceCategories' => $ecommerceCategories,
        ], $inputs['byVar']->all());

        return view('pages.frontend.ecommerce.shop.index', compact('data'));
    }
}
