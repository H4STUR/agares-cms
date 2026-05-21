<?php

namespace App\Http\Controllers\Frontend\Ecommerce;

use App\Http\Controllers\Frontend\PageController;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\Site;

class ProductController extends PageController
{
    public function show(string $slug)
    {
        $shopUrl = EcommerceSetting::where('key', 'shop_url')->value('value');

        if (!$shopUrl) abort(404, 'Shop page is not configured.');

        $site = Site::where('slug', $shopUrl)->first();
        if (!$site) abort(404, 'Shop site not found.');

        $product = Product::where('slug', $slug)
            ->where('status', 'published')
            ->with(['variants', 'defaultVariant', 'categories', 'tags'])
            ->firstOrFail();

        $inputs = $this->loadOwnerInputs(Site::class, $site->id);

        $data = array_merge([
            'site'              => $site,
            'product'           => $product,
            'content_site'      => $inputs['byVar'],
            'content_site_list' => $inputs['list'],
            'content'           => $inputs['byVar'],
            'content_list'      => $inputs['list'],
        ], $inputs['byVar']->all());

        return view('pages.frontend.ecommerce.products.show', compact('data'));
    }
}
