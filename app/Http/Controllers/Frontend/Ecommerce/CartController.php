<?php

namespace App\Http\Controllers\Frontend\Ecommerce;

use App\Http\Controllers\Frontend\PageController;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\Site;
use Illuminate\Http\Request;

class CartController extends PageController
{
    public function index()
    {
        [$site, $inputs] = $this->loadShopSite();

        $cartItems = $this->resolveCartItems();

        $data = array_merge([
            'site'              => $site,
            'content_site'      => $inputs['byVar'],
            'content_site_list' => $inputs['list'],
            'content'           => $inputs['byVar'],
            'content_list'      => $inputs['list'],
            'cartItems'         => $cartItems,
            'cartTotal'         => $cartItems->sum(fn ($i) => $i['subtotal']),
        ], $inputs['byVar']->all());

        return view('pages.frontend.ecommerce.cart.index', compact('data'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:ecommerce_products,id',
            'quantity'   => 'integer|min:1',
        ]);

        $productId = (int) $request->product_id;
        $qty       = max(1, (int) $request->get('quantity', 1));

        $cart = session()->get('cart', []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session()->put('cart', $cart);

        return back()->with('success', 'Item added to cart.');
    }

    public function update(Request $request, int $productId)
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        $cart = session()->get('cart', []);

        if ((int) $request->quantity === 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = (int) $request->quantity;
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(int $productId)
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);

        return back()->with('success', 'Item removed.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    // -------------------------------------------------------------------------

    private function loadShopSite(): array
    {
        $shopUrl = EcommerceSetting::where('key', 'shop_url')->value('value');
        if (!$shopUrl) abort(404, 'Shop page is not configured.');

        $site = Site::where('slug', $shopUrl)->firstOrFail();
        $inputs = $this->loadOwnerInputs(Site::class, $site->id);

        return [$site, $inputs];
    }

    private function resolveCartItems()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return collect();
        }

        $products = Product::whereIn('id', array_keys($cart))
            ->where('status', 'published')
            ->with('defaultVariant')
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function ($qty, $productId) use ($products) {
            $product = $products->get($productId);
            if (!$product) return null;

            $price    = $product->sale_price ?? $product->base_price;
            return [
                'product'  => $product,
                'quantity' => $qty,
                'price'    => $price,
                'subtotal' => $price * $qty,
            ];
        })->filter()->values();
    }
}
